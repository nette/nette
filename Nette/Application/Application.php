<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette::Application
 * @version    $Id$
 */

/*namespace Nette::Application;*/

/*use Nette::Environment;*/


require_once dirname(__FILE__) . '/../Object.php';



/**
 * Front Controller.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Application
 */
class Application extends /*Nette::*/Object
{
	/** @var int */
	public static $maxLoop = 20;

	/** @var array */
	public $defaultServices = array(
		'Nette::Application::IRouter' => 'Nette::Application::MultiRouter',
		'Nette::Application::IPresenterLoader' => 'Nette::Application::PresenterLoader',
	);

	/** @var bool */
	public $catchExceptions;

	/** @var array of function(Application $sender) */
	public $onStartup;

	/** @var array of function(Application $sender) */
	public $onShutdown;

	/** @var array of function(Application $sender) */
	public $onNewRequest;

	/** @var array of function(Application $sender, Exception $e) */
	public $onError;

	/** @var array of string */
	public $allowedMethods = array('GET', 'POST', 'HEAD');

	/** @var bool  automatically redirect to canonical URL */
	public $canonicalize = TRUE;

	/** @var string */
	public $errorPresenter;

	/** @var array of PresenterRequest */
	private $requests = array();

	/** @var Presenter */
	private $presenter;

	/** @var Nette::ServiceLocator */
	private $serviceLocator;



	/**
	 * Dispatch an HTTP request to a front controller.
	 */
	public function run()
	{
		if (version_compare(PHP_VERSION , '5.2.2', '<')) {
			throw new /*::*/ApplicationException('Nette::Application needs PHP 5.2.2 or newer.');
		}

		$httpRequest = Environment::getHttpRequest();
		$httpResponse = Environment::getHttpResponse();

		$httpResponse->setHeader('X-Powered-By: Nette Framework', TRUE);

		if (Environment::getVariable('baseUri') === NULL) {
			Environment::setVariable('baseUri', $httpRequest->getUri()->basePath);
		}

		// check HTTP method
		$method = $httpRequest->getMethod();
		if ($this->allowedMethods) {
			if (!in_array($method, $this->allowedMethods, TRUE)) {
				$httpResponse->setCode(/*Nette::Web::*/IHttpResponse::S501_NOT_IMPLEMENTED);
				$httpResponse->setHeader('Allow: ' . implode(',', $this->allowedMethods), TRUE);
				$method = htmlSpecialChars($method);
				die("<h1>Method $method is not implemented</h1>");
			}
		}

		// default router
		$router = $this->getRouter();
		if ($router instanceof MultiRouter && !count($router)) {
			$router[] = new SimpleRouter(array(
				'presenter' => 'Default',
				'view' => 'default',
			));
		}

		// dispatching
		$request = NULL;
		$hasError = FALSE;
		do {
			if (count($this->requests) > self::$maxLoop) {
				throw new ApplicationException('Too many loops detected in application life cycle.');
			}

			try {
				if (!$request) {
					// Routing
					$this->onStartup($this);

					$request = $router->match($httpRequest);
					if (!($request instanceof PresenterRequest)) {
						$request = NULL;
						throw new BadRequestException('No route for HTTP request.');
					}

					if ($request->getPresenterName() === $this->errorPresenter) {
						throw new BadRequestException('Invalid request.');
					}

					// redirect to canonicalized URI.
					if ($this->canonicalize && $method !== 'POST' && !$httpRequest->isAjax()) {
						$uri = $router->constructUrl($request, $httpRequest);
						if ($uri !== NULL && !$httpRequest->getUri()->isEqual($uri)) {
							throw new RedirectingException($uri, /*Nette::Web::*/IHttpResponse::S301_MOVED_PERMANENTLY);
						}
					}
				}

				$this->requests[] = $request;
				$this->onNewRequest($this);

				// Instantiate presenter
				$presenter = $request->getPresenterName();
				try {
					$class = $this->getPresenterLoader()->getPresenterClass($presenter);
					$request->adjustName($presenter);
				} catch (InvalidPresenterException $e) {
					throw new BadRequestException($e->getMessage());
				}
				$this->presenter = new $class($request);

				// Instantiate topmost service locator
				$this->presenter->setServiceLocator(new /*Nette::*/ServiceLocator($this->serviceLocator));

				// Execute presenter
				$this->presenter->run();
				break;

			} catch (RedirectingException $e) {
				// not error, presenter redirects to new URL
				$uri = $e->getUri();
				if (substr($uri, 0, 2) === '//') {
					$uri = $httpRequest->getUri()->scheme . ':' . $uri;
				} elseif (substr($uri, 0, 1) === '/') {
					$uri = $httpRequest->getUri()->hostUri . $uri;
				}
				$httpResponse->setCode($e->getCode());
				$httpResponse->setHeader('Location: ' . $uri);
				$httpResponse->setHeader('Connection: close');
				echo '<h1>Redirect</h1><p><a href="', htmlSpecialChars($uri), '">Please click here to continue</a>.</p>';
				break;

			} catch (ForwardingException $e) {
				// not error, presenter forwards to new request
				$request = $e->getRequest();

			} catch (AbortException $e) {
				// not error, application is correctly terminated
				break;

			} catch (Exception $e) {
				// fault barrier
				if ($hasError) {
					if (version_compare(PHP_VERSION , '5.3', '<')) {
						throw new ApplicationException('Cannot load error presenter');
					} else {
						throw new ApplicationException('Cannot load error presenter', 0, $e);
					}
				}

				$hasError = TRUE;
				$this->onError($this, $e);

				if ($this->catchExceptions === NULL) {
					$this->catchExceptions = Environment::isLive();
				}

				if (!$this->catchExceptions) {
					throw $e;
				}

				if ($this->errorPresenter) {
					$request = new PresenterRequest(
						$this->errorPresenter,
						PresenterRequest::FORWARD,
						array('exception' => $e)
					);

				} elseif ($e instanceof BadRequestException) {
					$httpResponse->setCode(404);
					echo '<title>404 Not Found</title><h1>Not Found</h1><p>The requested URL was not found on this server.</p>';
					break;

				} else {
					$httpResponse->setCode(500);
					echo '<title>500 Internal Server Error</title><h1>Server Error</h1>',
						'<p>The server encountered an internal error and was unable to complete your request.</p>';
					break;
				}
			}
		} while (1);

		$this->onShutdown($this);
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
	 * @return Nette::IServiceLocator
	 */
	final public function getServiceLocator()
	{
		if ($this->serviceLocator === NULL) {
			$this->serviceLocator = new /*Nette::*/ServiceLocator(Environment::getServiceLocator());

			foreach ($this->defaultServices as $name => $service) {
				$this->serviceLocator->addService($service, $name);
			}
		}
		return $this->serviceLocator;
	}



	/**
	 * Gets the service object of the specified type.
	 * @param  string service name
	 * @param  bool   throw exception if service doesn't exist?
	 * @return mixed
	 */
	final public function getService($name, $need = TRUE)
	{
		return $this->getServiceLocator()->getService($name, $need);
	}



	/**
	 * Returns router.
	 * @return IRouter
	 */
	public function getRouter()
	{
		return $this->getServiceLocator()->getService('Nette::Application::IRouter');
	}



	/**
	 * Change router. (experimental)
	 * @param  IRouter
	 * @return void
	 */
	public function setRouter(IRouter $router)
	{
		$this->getServiceLocator()->addService($router, 'Nette::Application::IRouter');
	}



	/**
	 * Returns presenter loader.
	 * @return IPresenterLoader
	 */
	public function getPresenterLoader()
	{
		return $this->getServiceLocator()->getService('Nette::Application::IPresenterLoader');
	}

}
