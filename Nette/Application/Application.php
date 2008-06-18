<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com/
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com/
 * @category   Nette
 * @package    Nette::Application
 */

/*namespace Nette::Application;*/

/*use Nette::Environment;*/


require_once dirname(__FILE__) . '/../Object.php';



/**
 * Check configuration.
 */
if (version_compare(PHP_VERSION , '5.2.2', '<')) {
	throw new /*::*/RuntimeException('Nette::Application needs PHP 5.2.2 or newer.');
}



/**
 * Front Controller.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Application
 * @version    $Revision$ $Date$
 */
class Application extends /*Nette::*/Object
{
	const MAX_LOOP = 20;

	/** @var array of function(Application $sender) */
	public $onStartup;

	/** @var array of function(Application $sender) */
	public $onShutdown;

	/** @var array of function(Application $sender) */
	public $onRouted;

	/** @var string */
	public $errorPresenter;// = 'Error';

	/** @var array of PresenterRequest */
	private $requests = array();

	/** @var Presenter */
	private $presenter;

	/** @var IRouter */
	private $router;

	/** @var IPresenterLoader */
	private $presenterLoader;

	/** @var ServiceLocator */
	private $locator;

	/** @var bool */
	private $hasError = FALSE;



	/**
	 * Dispatch an HTTP request to a front controller.
	 */
	public function run()
	{
		$httpRequest = Environment::getHttpRequest();
		$httpResponse = Environment::getHttpResponse();

		$httpResponse->setHeader('X-Powered-By: Nette Framework', TRUE);

		if (Environment::getVariable('baseUri') === NULL) {
			Environment::setVariable('baseUri', $httpRequest->getUri()->baseUri);
		}

		if (Environment::getVariable('basePath') === NULL) {
			Environment::setVariable('basePath', $httpRequest->getUri()->basePath);
		}

		// check HTTP method
		$method = $httpRequest->getMethod();
		$allowed = array('GET' => 1, 'POST' => 1, 'HEAD' => 1);
		if (!isset($allowed[$method])) {
			$httpResponse->setCode(501); // 501 Not Implemented
			$httpResponse->setHeader('Allow: ' . implode(', ', array_keys($allowed)), TRUE);
			die("Method $method not allowed.");
		}

		$this->onStartup($this);

		// dispatching
		$request = NULL;
		do {
			if (count($this->requests) > self::MAX_LOOP) {
				throw new ApplicationException('Infinite loop.');
			}

			try {
				// Routing
				if (!$request) {
					$request = $this->getRouter()->match($httpRequest);
					if (!($request instanceof PresenterRequest)) {
						$request = NULL;
						throw new ApplicationException('No route.');
					}

					$this->onRouted($this);
				}
				$this->requests[] = $request;

				// Instantiate presenter
				$presenter = $request->getPresenterName();
				$class = $this->getPresenterLoader()->getPresenterClass($presenter);
				$request->setPresenterName($presenter); // TODO: better!
				$this->presenter = new $class;

				// Instantiate topmost service locator
				$this->presenter->setServiceLocator(new /*Nette::*/ServiceLocator($this->locator));

				// Execute presenter
				$this->presenter->run($request);
				break;

			} catch (ForwardingException $e) {
				// not error, presenter forwards to new request
				$request = $e->getRequest();

			} catch (AbortException $e) {
				// not error, application is correctly aborted
				break;

			} catch (Exception $e) {
				// fault barrier
				if ($this->hasError || !$this->errorPresenter) {
					throw $e;
				}

				$this->hasError = TRUE;

				$request = new PresenterRequest(
					$this->errorPresenter,
					PresenterRequest::FORWARD,
					array(
						'exception' => $e,
					)
				);
			}
		} while (1);

		$this->onShutdown($this);
	}



	/**
	 * Gets the service locator (experimental).
	 * @return Nette::IServiceLocator
	 */
	final public function getServiceLocator()
	{
		if ($this->serviceLocator === NULL) {
			$this->serviceLocator = Environment::getServiceLocator();
		}
		return $this->serviceLocator;
	}



	/**
	 * @return array
	 */
	final public function getRequests()
	{
		return $this->requests;
	}



	/**
	 * @return Presenter
	 */
	final public function getPresenter()
	{
		return $this->presenter;
	}



	/**
	 * @return IRouter
	 */
	public function getRouter($init = NULL)
	{
		if ($this->router === NULL) {
			$this->router = new MultiRouter($init);
			// Environment::getService('Nette::Application::IRouter');
		}
		return $this->router;
	}



	/**
	 * @param  IRouter
	 * @return void
	 */
	public function setRouter(IRouter $router)
	{
		$this->router = $router;
	}



	/**
	 * Maps PresenterRequest object to absolute URI or path.
	 * @param  PresenterRequest
	 * @return string
	 * @throws ApplicationException
	 */
	public function constructUrl(PresenterRequest $request)
	{
		$uri = $this->getRouter()->constructUrl($request, Environment::getHttpRequest());
		if ($uri === NULL) {
			throw new ApplicationException('No route.');
		}
		return $uri;
	}



	/**
	 * @return IPresenterLoader
	 */
	public function getPresenterLoader()
	{
		if ($this->presenterLoader === NULL) {
			$this->presenterLoader = new PresenterLoader;
		}
		return $this->presenterLoader;
	}



	/**
	 * @param  IPresenterLoader
	 * @return void
	 */
	public function setPresenterLoader(IPresenterLoader $loader)
	{
		$this->presenterLoader = $loader;
	}

}
