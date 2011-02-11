<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Application;

use Nette;



/**
 * Front Controller.
 *
 * @author     David Grudl
 */
class Application extends Nette\Object
{
	/** @var int */
	public static $maxLoop = 20;

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

	/** @var array of function(Application $sender, IPresenterResponse $response); Occurs when a new response is received */
	public $onResponse;

	/** @var array of function(Application $sender, \Exception $e); Occurs when an unhandled exception occurs in the application */
	public $onError;

	/** @var array of string */
	public $allowedMethods = array('GET', 'POST', 'HEAD', 'PUT', 'DELETE');

	/** @var array of PresenterRequest */
	private $requests = array();

	/** @var Presenter */
	private $presenter;

	/** @var Nette\Context */
	private $context;



	/**
	 * Dispatch a HTTP request to a front controller.
	 * @return void
	 */
	public function run()
	{
		$httpRequest = $this->getHttpRequest();
		$httpResponse = $this->getHttpResponse();

		// check HTTP method
		if ($this->allowedMethods) {
			$method = $httpRequest->getMethod();
			if (!in_array($method, $this->allowedMethods, TRUE)) {
				$httpResponse->setCode(Nette\Web\IHttpResponse::S501_NOT_IMPLEMENTED);
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

					// autostarts session
					$session = $this->getSession();
					if (!$session->isStarted() && $session->exists()) {
						$session->start();
					}

					// routing
					$router = $this->getRouter();

					// enable routing debuggger
					Nette\Debug::addPanel(new RoutingDebugger($router, $httpRequest));

					$request = $router->match($httpRequest);
					if (!$request instanceof PresenterRequest) {
						$request = NULL;
						throw new BadRequestException('No route for HTTP request.');
					}

					if (strcasecmp($request->getPresenterName(), $this->errorPresenter) === 0) {
						throw new BadRequestException('Invalid request. Presenter is not achievable.');
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
				$this->presenter = $this->getPresenterFactory()->createPresenter($class);
				$response = $this->presenter->run($request);
				$this->onResponse($this, $response);

				// Send response
				if ($response instanceof ForwardingResponse) {
					$request = $response->getRequest();
					continue;

				} elseif ($response instanceof IPresenterResponse) {
					$response->send();
				}
				break;

			} catch (\Exception $e) {
				// fault barrier
				$this->onError($this, $e);

				if (!$this->catchExceptions) {
					$this->onShutdown($this, $e);
					throw $e;
				}

				if ($repeatedError) {
					$e = new ApplicationException('An error occured while executing error-presenter', 0, $e);
				}

				if (!$httpResponse->isSent()) {
					$httpResponse->setCode($e instanceof BadRequestException ? $e->getCode() : 500);
				}

				if (!$repeatedError && $this->errorPresenter) {
					$repeatedError = TRUE;
					if ($this->presenter instanceof Presenter) {
						try {
							$this->presenter->forward(":$this->errorPresenter:", array('exception' => $e));
						} catch (AbortException $foo) {
							$request = $this->presenter->getLastCreatedRequest();
						}
					} else {
						$request = new PresenterRequest(
							$this->errorPresenter,
							PresenterRequest::FORWARD,
							array('exception' => $e)
						);
					}
					// continue

				} else { // default error handler
					if ($e instanceof BadRequestException) {
						$code = $e->getCode();
					} else {
						$code = 500;
						Nette\Debug::log($e, Nette\Debug::ERROR);
					}
					require __DIR__ . '/templates/error.phtml';
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
	 * Gets the context.
	 * @return Application  provides a fluent interface
	 */
	public function setContext(Nette\IContext $context)
	{
		$this->context = $context;
		return $this;
	}



	/**
	 * Gets the context.
	 * @return Nette\IContext
	 */
	final public function getContext()
	{
		return $this->context;
	}



	/**
	 * Gets the service object of the specified type.
	 * @param  string service name
	 * @param  array  options in case service is not singleton
	 * @return object
	 */
	final public function getService($name, array $options = NULL)
	{
		return $this->context->getService($name, $options);
	}



	/**
	 * Returns router.
	 * @return IRouter
	 */
	public function getRouter()
	{
		return $this->context->getService('Nette\\Application\\IRouter');
	}



	/**
	 * Changes router.
	 * @param  IRouter
	 * @return Application  provides a fluent interface
	 */
	public function setRouter(IRouter $router)
	{
		$this->context->addService('Nette\\Application\\IRouter', $router);
		return $this;
	}



	/**
	 * Returns presenter loader.
	 * @return IPresenterLoader
	 */
	public function getPresenterLoader()
	{
		return $this->context->getService('Nette\\Application\\IPresenterLoader');
	}



	/**
	 * Returns presenter factory.
	 * @return IPresenterFactory
	 */
	public function getPresenterFactory()
	{
		return $this->context->getService('Nette\\Application\\IPresenterFactory');
	}



	/**
	 * @return Nette\Web\IHttpRequest
	 */
	protected function getHttpRequest()
	{
		return $this->context->getService('Nette\\Web\\IHttpRequest');
	}



	/**
	 * @return Nette\Web\IHttpResponse
	 */
	protected function getHttpResponse()
	{
		return $this->context->getService('Nette\\Web\\IHttpResponse');
	}



	/**
	 * @return Nette\Web\Session
	 */
	protected function getSession($namespace = NULL)
	{
		$handler = $this->context->getService('Nette\\Web\\Session');
		return $namespace === NULL ? $handler : $handler->getNamespace($namespace);
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
			$key = Nette\String::random(5);
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
			$this->presenter->sendResponse(new ForwardingResponse($request));
		}
	}

}
