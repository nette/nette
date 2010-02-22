<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Application
 */

/*namespace Nette\Application;*/

/*use Nette\Environment;*/



/**
 * Front Controller.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Application
 */
class Application extends /*Nette\*/Object
{
	/** @var int */
	public static $maxLoop = 20;

	/** @var array */
	public $defaultServices = array(
		'Nette\Application\IRouter' => 'Nette\Application\MultiRouter',
		'Nette\Application\IPresenterLoader' => array(__CLASS__, 'createPresenterLoader'),
	);

	/** @var bool enable fault barrier? */
	public $catchExceptions;

	/** @var string */
	public $errorPresenter;

	/** @var array of function(Application $sender); Occurs before the application loads presenter */
	public $onStartup;

	/** @var array of function(Application $sender, \Exception $e = NULL); Occurs before the application shuts down */
	public $onShutdown;

	/** @var array of function(Application $sender, PresenterRequest $request); Occurs when a new request is ready for dispatch */
	public $onRequest;

	/** @var array of function(Application $sender, \Exception $e); Occurs when an unhandled exception occurs in the application */
	public $onError;

	/** @var array of string */
	public $allowedMethods = array('GET', 'POST', 'HEAD', 'PUT', 'DELETE');

	/** @var array of PresenterRequest */
	private $requests = array();

	/** @var Presenter */
	private $presenter;

	/** @var Nette\ServiceLocator */
	private $serviceLocator;



	/**
	 * Dispatch a HTTP request to a front controller.
	 * @return void
	 */
	public function run()
	{
		$httpRequest = $this->getHttpRequest();
		$httpResponse = $this->getHttpResponse();

		$httpRequest->setEncoding('UTF-8');
		$httpResponse->setHeader('X-Powered-By', 'Nette Framework');

		if (Environment::getVariable('baseUri') === NULL) {
			Environment::setVariable('baseUri', $httpRequest->getUri()->getBasePath());
		}

        // autostarts session
		$session = $this->getSession();
		if (!$session->isStarted() && $session->exists()) {
			$session->start();
		}

		// check HTTP method
		if ($this->allowedMethods) {
			$method = $httpRequest->getMethod();
			if (!in_array($method, $this->allowedMethods, TRUE)) {
				$httpResponse->setCode(/*Nette\Web\*/IHttpResponse::S501_NOT_IMPLEMENTED);
				$httpResponse->setHeader('Allow', implode(',', $this->allowedMethods));
				echo '<h1>Method ' . htmlSpecialChars($method) . ' is not implemented</h1>';
				return;
			}
		}

		// dispatching
		$request = NULL;
		$repeatedError = FALSE;
		do {
			try {
				if (count($this->requests) > self::$maxLoop) {
					throw new ApplicationException('Too many loops detected in application life cycle.');
				}

				if (!$request) {
					$this->onStartup($this);

					// default router
					$router = $this->getRouter();
					if ($router instanceof MultiRouter && !count($router)) {
						$router[] = new SimpleRouter(array(
							'presenter' => 'Default',
							'action' => 'default',
						));
					}

					// routing
					$request = $router->match($httpRequest);
					if (!($request instanceof PresenterRequest)) {
						$request = NULL;
						throw new BadRequestException('No route for HTTP request.');
					}

					if (strcasecmp($request->getPresenterName(), $this->errorPresenter) === 0) {
						throw new BadRequestException('Invalid request.');
					}
				}

				$this->requests[] = $request;
				$this->onRequest($this, $request);

				// Instantiate presenter
				$presenter = $request->getPresenterName();
				try {
					$class = $this->getPresenterLoader()->getPresenterClass($presenter);
					$request->setPresenterName($presenter);
				} catch (InvalidPresenterException $e) {
					throw new BadRequestException($e->getMessage(), 404, $e);
				}
				$request->freeze();

				// Execute presenter
				$this->presenter = new $class;
				$response = $this->presenter->run($request);

				// Send response
				if ($response instanceof ForwardingResponse) {
					$request = $response->getRequest();
					continue;

				} elseif ($response instanceof IPresenterResponse) {
					$response->send();
				}
				break;

			} catch (/*\*/Exception $e) {
				// fault barrier
				if ($this->catchExceptions === NULL) {
					$this->catchExceptions = Environment::isProduction();
				}

				if (!$this->catchExceptions) {
					throw $e;
				}

				$this->onError($this, $e);

				if ($repeatedError) {
					$e = new ApplicationException('An error occured while executing error-presenter', 0, $e);
				}

				if (!$httpResponse->isSent()) {
					$httpResponse->setCode($e instanceof BadRequestException ? $e->getCode() : 500);
				}

				if (!$repeatedError && $this->errorPresenter) {
					$repeatedError = TRUE;
					$request = new PresenterRequest(
						$this->errorPresenter,
						PresenterRequest::FORWARD,
						array('exception' => $e)
					);
					// continue

				} else { // default error handler
					echo "<meta name='robots' content='noindex'>\n\n";
					if ($e instanceof BadRequestException) {
						echo "<title>404 Not Found</title>\n\n<h1>Not Found</h1>\n\n<p>The requested URL was not found on this server.</p>";

					} else {
						/*Nette\*/Debug::processException($e, FALSE);
						echo "<title>500 Internal Server Error</title>\n\n<h1>Server Error</h1>\n\n",
							"<p>The server encountered an internal error and was unable to complete your request. Please try again later.</p>";
					}
					echo "\n\n<hr>\n<small><i>Nette Framework</i></small>";
					break;
				}
			}
		} while (1);

		$this->onShutdown($this, isset($e) ? $e : NULL);
	}



	/**
	 * Returns all processed requests.
	 * @return array of PresenterRequest
	 */
	final public function getRequests()
	{
		return $this->requests;
	}



	/**
	 * Returns current presenter.
	 * @return Presenter
	 */
	final public function getPresenter()
	{
		return $this->presenter;
	}



	/********************* services ****************d*g**/



	/**
	 * Gets the service locator (experimental).
	 * @return Nette\IServiceLocator
	 */
	final public function getServiceLocator()
	{
		if ($this->serviceLocator === NULL) {
			$this->serviceLocator = new /*Nette\*/ServiceLocator(Environment::getServiceLocator());

			foreach ($this->defaultServices as $name => $service) {
				if (!$this->serviceLocator->hasService($name)) {
					$this->serviceLocator->addService($name, $service);
				}
			}
		}
		return $this->serviceLocator;
	}



	/**
	 * Gets the service object of the specified type.
	 * @param  string service name
	 * @param  array  options in case service is not singleton
	 * @return object
	 */
	final public function getService($name, array $options = NULL)
	{
		return $this->getServiceLocator()->getService($name, $options);
	}



	/**
	 * Returns router.
	 * @return IRouter
	 */
	public function getRouter()
	{
		return $this->getServiceLocator()->getService('Nette\Application\IRouter');
	}



	/**
	 * Changes router.
	 * @param  IRouter
	 * @return Application  provides a fluent interface
	 */
	public function setRouter(IRouter $router)
	{
		$this->getServiceLocator()->addService('Nette\Application\IRouter', $router);
		return $this;
	}



	/**
	 * Returns presenter loader.
	 * @return IPresenterLoader
	 */
	public function getPresenterLoader()
	{
		return $this->getServiceLocator()->getService('Nette\Application\IPresenterLoader');
	}



	/********************* service factories ****************d*g**/



	/**
	 * @return IPresenterLoader
	 */
	public static function createPresenterLoader()
	{
		return new PresenterLoader(Environment::getVariable('appDir'));
	}



	/********************* request serialization ****************d*g**/



	/**
	 * Stores current request to session.
	 * @param  mixed  optional expiration time
	 * @return string key
	 */
	public function storeRequest($expiration = '+ 10 minutes')
	{
		$session = $this->getSession('Nette.Application/requests');
		do {
			$key = substr(md5(lcg_value()), 0, 4);
		} while (isset($session[$key]));

		$session[$key] = end($this->requests);
		$session->setExpiration($expiration, $key);
		return $key;
	}



	/**
	 * Restores current request to session.
	 * @param  string key
	 * @return void
	 */
	public function restoreRequest($key)
	{
		$session = $this->getSession('Nette.Application/requests');
		if (isset($session[$key])) {
			$request = clone $session[$key];
			unset($session[$key]);
			$request->setFlag(PresenterRequest::RESTORED, TRUE);
			$this->presenter->terminate(new ForwardingResponse($request));
		}
	}



	/********************* backend ****************d*g**/



	/**
	 * @return Nette\Web\IHttpRequest
	 */
	protected function getHttpRequest()
	{
		return Environment::getHttpRequest();
	}



	/**
	 * @return Nette\Web\IHttpResponse
	 */
	protected function getHttpResponse()
	{
		return Environment::getHttpResponse();
	}



	/**
	 * @return Nette\Web\Session
	 */
	protected function getSession($namespace = NULL)
	{
		return Environment::getSession($namespace);
	}

}
