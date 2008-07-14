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
 * Front Controller.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Application
 * @version    $Revision$ $Date$
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
	public $onRouted;

	/** @var array of function(Application $sender, Exception $e) */
	public $onError;

	/** @var array of string */
	public $allowedMethods = array('GET', 'POST', 'HEAD');

	/** @var string */
	public $errorPresenter = 'Error';

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
		if ($this->allowedMethods) {
			$method = $httpRequest->getMethod();
			if (!in_array($method, $this->allowedMethods, TRUE)) {
				$httpResponse->setCode(/*Nette::Web::*/IHttpResponse::S501_NOT_IMPLEMENTED);
				$httpResponse->setHeader('Allow: ' . implode(',', $this->allowedMethods), TRUE);
				$method = htmlSpecialChars($method);
				die("<h1>Method $method is not implemented</h1>");
			}
		}

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
				// Routing
				if (!$request) {
					$this->onStartup($this);

					$request = $router->match($httpRequest);
					if (!($request instanceof PresenterRequest)) {
						$request = NULL;
						throw new BadRequestException('No route for HTTP request.');
					}

					$this->onRouted($this);
				}
				$this->requests[] = $request;

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

			} catch (ForwardingException $e) {
				// not error, presenter forwards to new request
				$request = $e->getRequest();

			} catch (AbortException $e) {
				// not error, application is correctly aborted
				break;

			} catch (Exception $e) {
				// fault barrier
				if ($hasError) {
					throw new ApplicationException('Cannot load error presenter', 0, $e);
				}

				$hasError = TRUE;
				$this->onError($this, $e);

				if ($this->catchExceptions === NULL) {
					$this->catchExceptions = Environment::getName() !== Environment::DEVELOPMENT;
				}

				if ($this->catchExceptions && $this->errorPresenter) {
					$request = new PresenterRequest(
						$this->errorPresenter,
						PresenterRequest::FORWARD,
						array('exception' => $e)
					);
					continue;
				}

				throw $e;
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
	 * @param  bool
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
