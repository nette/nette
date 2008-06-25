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

	/** @var string */
	public $errorPresenter = 'Error';

	/** @var array of PresenterRequest */
	private $requests = array();

	/** @var Presenter */
	private $presenter;

	/** @var Nette::ServiceLocator */
	private $serviceLocator;

	/** @var bool */
	private $hasError;



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

		if ($this->catchExceptions === NULL) {
			$this->catchExceptions = Environment::getName() !== Environment::DEVELOPMENT;
		}

		// check HTTP method
		$method = $httpRequest->getMethod();
		$allowed = array('GET' => 1, 'POST' => 1, 'HEAD' => 1);
		if (!isset($allowed[$method])) {
			$httpResponse->setCode(/*Nette::Web::*/IHttpResponse::S501_NOT_IMPLEMENTED);
			$httpResponse->setHeader('Allow: ' . implode(', ', array_keys($allowed)), TRUE);
			die("Method $method not allowed.");
		}

		// dispatching
		$request = NULL;
		$this->hasError = FALSE;
		do {
			if (count($this->requests) > self::MAX_LOOP) {
				throw new ApplicationException('Infinite loop.');
			}

			try {
				// Routing
				if (!$request) {
					$this->onStartup($this);

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
				$this->presenter->setServiceLocator(new /*Nette::*/ServiceLocator($this->serviceLocator));

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
				if ($this->hasError || !$this->catchExceptions || !$this->errorPresenter) {
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
