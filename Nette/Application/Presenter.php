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



require_once dirname(__FILE__) . '/../Application/Control.php';

require_once dirname(__FILE__) . '/../Application/IPresenter.php';



/**
 * Presenter object represents a webpage instance. It executes all the logic for the request.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Application
 */
abstract class Presenter extends Control implements IPresenter
{
	/** life cycle phases @see getPhase() */
	const PHASE_STARTUP = 1;
	const PHASE_PREPARE = 2;
	const PHASE_SIGNAL = 3;
	const PHASE_RENDER = 4;
	const PHASE_SHUTDOWN = 5;

	/** bad link handling @see $invalidLinkMode */
	const INVALID_LINK_SILENT = 1;
	const INVALID_LINK_WARNING = 2;
	const INVALID_LINK_EXCEPTION = 3;

	/** special parameters */
	const SIGNAL_KEY = 'do';
	const VIEW_KEY = 'view';

	/** @var string */
	public static $defaultView = 'default';

	/** @var int */
	public static $invalidLinkMode;

	/** @var PresenterRequest */
	private $request;

	/** @var int */
	private $phase;

	/** @var bool  automatically call canonicalize() */
	public $autoCanonicalize = TRUE;

	/**
	 * Lists of all components identified by a uniqueId starting from this page.
	 * @var array of Nette::IComponent
	 */
	private $globalComponents = array();

	/** @var array */
	private $globalParams;

	/** @var array */
	private $globalState;

	/** @var string */
	private $view;

	/** @var string */
	private $scene;

	/** @var string */
	private $layout = 'layout';

	/** @var IAjaxDriver */
	private $ajaxDriver;

	/** @var string */
	private $signalReceiver;

	/** @var string */
	private $signal;

	/** @var bool */
	private $ajaxMode;

	/** @var Application */
	private $application;

	/** @var IRouter  cached value for createRequest() & createSubRequest() */
	private $router;

	/** @var IPresenterLoader  cached value for createRequest() & createSubRequest() */
	private $presenterLoader;

	/** @var Nette::Web::IHttpRequest  cached value for better performance */
	private $httpRequest;



	/**
	 * @param  PresenterRequest
	 */
	public function __construct(PresenterRequest $request)
	{
		$this->request = $request;
		$this->httpRequest = Environment::getHttpRequest();
		$this->application = Environment::getApplication();
		$this->router = $this->application->getRouter();
		$this->presenterLoader = $this->application->getPresenterLoader();

		parent::__construct(NULL, $request->getPresenterName());
	}



	/**
	 * @param  bool
	 * @return PresenterRequest
	 */
	final public function getRequest($clone = TRUE)
	{
		return $clone ? clone $this->request : $this->request;
	}



	/**
	 * Returns self.
	 * @return Presenter
	 */
	final public function getPresenter($need = TRUE)
	{
		return $this;
	}



	/**
	 * Returns a name that uniquely identifies component.
	 * @return string
	 */
	final public function getUniqueId()
	{
		return '';
	}



	/**
	 * Returns application object.
	 * @return Application
	 */
	final public function getApplication()
	{
		return $this->application;
	}



	/********************* interface IPresenter ****************d*g**/



	/**
	 * @return void
	 * @throws AbortException
	 */
	public function run()
	{
		try {
			// PHASE 1: STARTUP
			$this->phase = self::PHASE_STARTUP;
			if ($this->isAjax()) {
				$this->getAjaxDriver()->open(Environment::getHttpResponse());
				self::$outputAllowed = FALSE;
			}
			$this->initGlobalParams();
			$this->registerComponent($this->getUniqueId(), $this);
			$this->startup();
			// calls $this->present{view}();
			$this->tryCall($this->formatPresentMethod($this->getView()), $this->params);

			if ($this->autoCanonicalize) {
				$this->canonicalize();
			}
			if ($this->httpRequest->isMethod('head')) {
				$this->terminate();
			}

			// PHASE 2: PREPARING SCENE
			$this->phase = self::PHASE_PREPARE;
			$this->beforePrepare();
			// calls $this->prepare{scene}();
			$this->tryCall($this->formatPrepareMethod($this->getScene()), $this->params);

			// PHASE 3: SIGNAL HANDLING
			$this->phase = self::PHASE_SIGNAL;
			$this->processSignal();

			// save component tree persistent state
			$this->saveGlobalState();
			if ($this->isAjax()) {
				$this->ajaxDriver->updateState($this->globalState[NULL]);
			}

			// PHASE 4: RENDERING SCENE
			$this->phase = self::PHASE_RENDER;

			$this->beforeRender();
			// calls $this->render{scene}();
			$this->tryCall($this->formatRenderMethod($this->getScene()), $this->params);

			$this->renderTemplate();

		} catch (AbortException $e) {
			// continue with shutting down
		} /* finally */ {
			// PHASE 5: SHUTDOWN
			if ($this->isAjax()) {
				$this->ajaxDriver->close();
			}
			$this->phase = self::PHASE_SHUTDOWN;
			$this->unregisterComponent($this);
			$this->shutdown();
			if (isset($e)) throw $e;
		}
	}



	/**
	 * Returns current presenter life cycle phase.
	 * @return int
	 */
	final public function getPhase()
	{
		return $this->phase;
	}



	/**
	 * @return void
	 */
	protected function startup()
	{
	}



	/**
	 * Common prepare method.
	 * @return void
	 */
	protected function beforePrepare()
	{
	}



	/**
	 * Common render method.
	 * @return void
	 */
	protected function beforeRender()
	{
	}



	/**
	 * @return void
	 */
	protected function shutdown()
	{
	}



	/********************* signal handling ****************d*g**/



	/**
	 * @return void
	 * @throws BadSignalException
	 */
	final protected function processSignal()
	{
		if ($this->signal === NULL) return;

		if (!isset($this->globalComponents[$this->signalReceiver])) {
			throw new BadSignalException("The signal receiver component '$this->signalReceiver' is not found.");
		}

		$component = $this->globalComponents[$this->signalReceiver];
		if (!$component instanceof ISignalReceiver) {
			throw new BadSignalException("The signal receiver component '$this->signalReceiver' is not ISignalReceiver implementor.");
		}

		// auto invalidate
		if ($component instanceof IRenderable) {
			$component->invalidateControl();
		}

		$component->signalReceived($this->signal);
		$this->signal = NULL;
	}



	/**
	 * @param  bool  component or its id?
	 * @return string|ISignalReceiver|NULL
	 */
	final public function getSignalReceiver($returnId = FALSE)
	{
		if ($returnId) {
			return $this->signalReceiver;

		} elseif (isset($this->globalComponents[$this->signalReceiver])) {
			return $this->globalComponents[$this->signalReceiver];

		} else {
			return NULL;
		}
	}



	/**
	 * @return string|NULL
	 */
	final public function getSignal()
	{
		return $this->signal;
	}



	/********************* rendering ****************d*g**/



	/**
	 * Returns current view name.
	 * @return string
	 */
	final public function getView($fullyQualified = FALSE)
	{
		return $fullyQualified ? ':' . $this->getName() . ':' . $this->view : $this->view;
	}



	/**
	 * Changes current view.
	 * @param  string
	 * @return void
	 */
	public function changeView($view)
	{
		if (preg_match("#^[a-zA-Z0-9][a-zA-Z0-9_\x7f-\xff]*$#", $view)) {
			$this->view = $view;

		} else {
			throw new BadRequestException("View name '$view' is not alphanumeric string.");
		}
		$this->changeScene($view);
	}



	/**
	 * Returns current scene name.
	 * @return string
	 */
	final public function getScene()
	{
		return $this->scene;
	}



	/**
	 * Changes current view scene.
	 * @param  string
	 * @return void
	 */
	public function changeScene($scene)
	{
		$this->scene = $scene;
	}



	/**
	 * Returns current layout name.
	 * @return string|FALSE
	 */
	final public function getLayout()
	{
		return $this->layout;
	}



	/**
	 * Changes or disables layout.
	 * @param  string|FALSE
	 * @return void
	 */
	public function changeLayout($layout)
	{
		$this->layout = $layout;
	}



	/**
	 * @return void
	 * @throws BadRequestException if no template found
	 */
	protected function renderTemplate()
	{
		$template = $this->getTemplate();
		if (!$template) return;

		if ($template instanceof /*Nette::Templates::*/Template && !$template->getFile()) {
			$presenter = $this->getName();
			$hasContent = $hasLayout = FALSE;

			if ($this->layout) {
				foreach ($this->formatLayoutTemplateFiles($presenter, $this->layout) as $file) {
					if (is_file($file)) {
						$template->setFile($file);
						$hasLayout = TRUE;
						break;
					}
				}
			}

			foreach ($this->formatTemplateFiles($presenter, $this->scene) as $file) {
				if (is_file($file)) {
					if ($hasLayout) { // has layout?
						$template->addTemplate('content', $file);
					} else {
						$template->setFile($file);
					}
					$hasContent = TRUE;
					break;
				}
			}

			if (!$hasContent) {
				throw new BadRequestException("Page not found. Missing template '$file'.");
			}
		}

		$template->render();
	}



	/**
	 * Formats layout template file names.
	 * @param  string
	 * @param  string
	 * @return array
	 */
	protected function formatLayoutTemplateFiles($presenter, $layout)
	{
		$root = Environment::getVariable('templatesDir');
		$presenter = str_replace(':', 'Module/', $presenter);
		$module = substr($presenter, 0, (int) strrpos($presenter, '/'));
		$base = '';
		if ($root === Environment::getVariable('presentersDir')) {
			$base = 'templates/';
			if ($module === '') {
				$presenter = 'templates/' . $presenter;
			} else {
				$presenter = substr_replace($presenter, '/templates', strrpos($presenter, '/'), 0);
			}
		}

		return array(
			"$root/$presenter/@$layout.phtml",
			"$root/$presenter.@$layout.phtml",
			"$root/$module/$base@$layout.phtml",
			"$root/$base@$layout.phtml",
		);
	}



	/**
	 * Formats scene template file names.
	 * @param  string
	 * @param  string
	 * @return array
	 */
	protected function formatTemplateFiles($presenter, $scene)
	{
		$root = Environment::getVariable('templatesDir');
		$presenter = str_replace(':', 'Module/', $presenter);
		if ($root === Environment::getVariable('presentersDir')) {
			$pos = strrpos($presenter, '/');
			$presenter = $pos === FALSE ? 'templates/' . $presenter : substr_replace($presenter, '/templates', $pos, 0);
		}
		return array(
			"$root/$presenter/$scene.phtml",
			"$root/$presenter.$scene.phtml",
		);
	}



	/**
	 * Formats execute method name.
	 * @param  string
	 * @return string
	 */
	protected static function formatPresentMethod($view)
	{
		return $view == NULL ? NULL : 'present' . $view; // intentionally ==
	}



	/**
	 * Formats prepare scene method name.
	 * @param  string
	 * @return string
	 */
	protected static function formatPrepareMethod($scene)
	{
		return $scene == NULL ? NULL : 'prepare' . $scene; // intentionally ==
	}



	/**
	 * Formats render scene method name.
	 * @param  string
	 * @return string
	 */
	protected static function formatRenderMethod($scene)
	{
		return $scene == NULL ? NULL : 'render' . $scene; // intentionally ==
	}



	/********************* partial AJAX rendering ****************d*g**/



	/**
	 * Is AJAX request?
	 * @return bool
	 */
	public function isAjax()
	{
		if ($this->ajaxMode === NULL) {
			$this->ajaxMode = $this->httpRequest->isAjax();
		}
		return $this->ajaxMode;
	}



	/**
	 * @return IAjaxDriver|NULL
	 */
	public function getAjaxDriver()
	{
		if ($this->ajaxDriver === NULL) {
			$value = $this->createAjaxDriver();
			if (!($value instanceof IAjaxDriver)) {
				$class = get_class($value);
				throw new /*::*/UnexpectedValueException("The Nette::Application::IAjaxDriver object was expected, '$class' was given.");
			}
			$this->ajaxDriver = $value;
		}
		return $this->ajaxDriver;
	}



	/**
	 * @return IAjaxDriver
	 */
	protected function createAjaxDriver()
	{
		return new AjaxDriver;
	}



	/********************* navigation & flow ****************d*g**/



	/**
	 * Generates URL to presenter, view or signal.
	 * @param  string
	 * @param  array|mixed
	 * @return string
	 * @throws InvalidLinkException
	 */
	public function link($destination, $args = array())
	{
		if (!is_array($args)) {
			$args = func_get_args();
			array_shift($args);
		}

		try {
			if (substr($destination, -1) === '!') {
				return parent::link($destination, $args);

			} else {
				return $this->createRequest($destination, $args);
			}

		} catch (InvalidLinkException $e) {
			return $this->handleInvalidLink($e);
		}
	}



	/**
	 * Forward to another presenter or view.
	 * @param  string|PresenterRequest
	 * @param  array|mixed
	 * @return void
	 * @throws ForwardingException
	 */
	public function forward($destination, $args = array())
	{
		if ($destination instanceof PresenterRequest) {
			throw new ForwardingException($destination);

		} elseif (!is_array($args)) {
			$args = func_get_args();
			array_shift($args);
		}

		throw new ForwardingException($this->createRequest($destination, $args, FALSE));
	}



	/**
	 * Redirect to another URL and ends presenter execution.
	 * @param  string
	 * @param  int HTTP error code
	 * @return void
	 * @throws RedirectingException
	 */
	public function redirectUri($uri, $code = /*Nette::Web::*/IHttpResponse::S303_POST_GET)
	{
		if ($this->isAjax()) {
			$this->ajaxDriver->redirect($uri);
			$this->terminate();

		} else {
			throw new RedirectingException($uri, $code);
		}
	}



	/**
	 * Link to myself.
	 * @return string
	 */
	public function backlink()
	{
		return $this->getView(TRUE);
	}



	/**
	 * Correctly terminates presenter.
	 * @return void
	 * @throws AbortException
	 */
	public function terminate()
	{
		throw new AbortException();
	}



	/**
	 * Conditional redirect to canonicalized URI.
	 * @return void
	 * @throws AbortException
	 */
	public function canonicalize()
	{
		if (!$this->isAjax() && ($this->request->isMethod('get') || $this->request->isMethod('head'))) {
			$uri = $this->createRequest('self', array());
			if ($uri !== NULL && !$this->httpRequest->getUri()->isEqual($uri)) {
				throw new RedirectingException($uri, /*Nette::Web::*/IHttpResponse::S301_MOVED_PERMANENTLY);
			}
		}
	}



	/**
	 * Attempts to cache the sent entity by its last modification date
	 * @param  int    last modified time as unix timestamp
	 * @param  string strong entity tag validator
	 * @param  int    optional expiration time
	 * @return int    date of the client's cache version, if available
	 * @throws AbortException
	 */
	public function lastModified($lastModified, $etag = NULL, $expire = NULL)
	{
		if (!Environment::isLive()) {
			return;
		}

		$httpResponse = Environment::getHttpResponse();
		$match = FALSE;

		if ($lastModified > 0) {
			$httpResponse->setHeader('Last-Modified', /*Nette::Web::*/HttpResponse::date($lastModified));
		}

		if ($etag != NULL) { // intentionally ==
			$etag = '"' . addslashes($etag) . '"';
			$httpResponse->setHeader('ETag', $etag);
		}

		if ($expire !== NULL) {
			$httpResponse->expire($expire);
		}

		$ifNoneMatch = $this->httpRequest->getHeader('if-none-match');
		$ifModifiedSince = $this->httpRequest->getHeader('if-modified-since');
		if ($ifModifiedSince !== NULL) {
			$ifModifiedSince = strtotime($ifModifiedSince);
		}

		if ($ifNoneMatch !== NULL) {
			if ($ifNoneMatch === '*') {
				$match = TRUE; // match, check if-modified-since

			} elseif ($etag == NULL || strpos(' ' . strtr($ifNoneMatch, ",\t", '  '), ' ' . $etag) === FALSE) {
				return $ifModifiedSince; // no match, ignore if-modified-since

			} else {
				$match = TRUE; // match, check if-modified-since
			}
		}

		if ($ifModifiedSince !== NULL) {
			if ($lastModified > 0 && $lastModified <= $ifModifiedSince) {
				$match = TRUE;

			} else {
				return $ifModifiedSince;
			}
		}

		if ($match) {
			$httpResponse->setCode(/*Nette::Web::*/IHttpResponse::S304_NOT_MODIFIED);
			$httpResponse->setHeader('Connection', 'close');
			$this->terminate();

		} else {
			return $ifModifiedSince;
		}
	}



	/**
	 * Invalid link handler.
	 * @param  InvalidLinkException
	 * @return string
	 * @throws InvalidLinkException
	 */
	protected function handleInvalidLink($e)
	{
		if (self::$invalidLinkMode === NULL) {
			self::$invalidLinkMode = Environment::isLive()
				? self::INVALID_LINK_SILENT : self::INVALID_LINK_WARNING;
		}

		if (self::$invalidLinkMode === self::INVALID_LINK_SILENT) {
			return '#';

		} elseif (self::$invalidLinkMode === self::INVALID_LINK_WARNING) {
			return 'error: ' . htmlSpecialChars($e->getMessage());

		} else { // self::INVALID_LINK_EXCEPTION
			throw $e;
		}
	}



	/**
	 * PresenterRequest/URL factory.
	 * @param  string   destination in format "[[module:]presenter:][view]"
	 * @param  array    array of arguments
	 * @param  bool     return PresenterRequest or URL?
	 * @return string|PresenterRequest
	 * @throws InvalidLinkException
	 */
	protected function createRequest($destination, array $args, $returnUri = TRUE)
	{
		// TODO: add cache here!
		if ($destination == NULL) {  // intentionally ==
			throw new InvalidLinkException("Destination must be non-empty string.");
		}

		$a = strrpos($destination, ':');
		if ($a === FALSE) {
			$view = $destination === 'this' || $destination === 'self' ? $this->view : $destination;
			$presenter = $this->getName();
			$presenterClass = $this->getClass();

		} else {
			$view = (string) substr($destination, $a + 1);
			if ($destination[0] === ':') {
				if ($a < 2) {
					throw new InvalidLinkException("Missing presenter name in '$destination'.");
				}
				$presenter = substr($destination, 1, $a - 1);

			} else {
				$presenter = $this->getName();
				$b = strrpos($presenter, ':');
				if ($b === FALSE) {
					$presenter = substr($destination, 0, $a);
				} else {
					$presenter = substr($presenter, 0, $b + 1) . substr($destination, 0, $a);
				}
			}
			$presenterClass = $this->presenterLoader->getPresenterClass($presenter);
		}

		if (is_subclass_of($presenterClass, __CLASS__)) {
			if ($view === '') {
				/*$view = $presenterClass::$defaultView;*/ // in PHP 5.3
				/**/$view = self::$defaultView;/**/
			}

			if ($args) {
				/*$method = $presenterClass::formatPresentMethod($view);*/ // in PHP 5.3
				/**/$method = call_user_func(array($presenterClass, 'formatPresentMethod'), $view);/**/
				if (!PresenterHelpers::isMethodCallable($presenterClass, $method)) {
					/*$method = $presenterClass::formatRenderMethod($view);*/ // in PHP 5.3
					/**/$method = call_user_func(array($presenterClass, 'formatRenderMethod'), $view);/**/
					if (!PresenterHelpers::isMethodCallable($presenterClass, $method)) {
						$method = NULL;
					}
				}

				if ($method !== NULL) {
					PresenterHelpers::argsToParams($presenterClass, $method, $args);

				} elseif (array_key_exists(0, $args)) { // is needed argument -> params convertion?
					throw new InvalidLinkException("Extra parameter for '$presenter:$view'.");
				}
			}

			$this->saveGlobalState();
			$this->saveState($args, $presenterClass);

			if ($destination === 'self') { // experimental
				$args += $this->request->getParams();
			}
		}

		$args[self::VIEW_KEY] = $view;

		$request = new PresenterRequest(
			$presenter,
			PresenterRequest::FORWARD,
			$args
		);

		return $returnUri ? $this->constructUrl($request) : $request;
	}



	/**
	 * PresenterRequest/URL factory.
	 * @param  string   optional signal executor
	 * @param  string   optional signal to execute
	 * @param  array    optional signal arguments
	 * @param  bool     return PresenterRequest or URL?
	 * @return string|PresenterRequest
	 * @throws InvalidLinkException
	 */
	protected function createSubRequest($componentId, $signal, $cparams, $returnUri = TRUE)
	{
		// TODO: add cache here!
		$presenterClass = $this->getClass();
		$view = $this->view;
		$params = array();

		$method = $this->formatPresentMethod($view);
		if (!PresenterHelpers::isMethodCallable($presenterClass, $method)) {
			$method = $this->formatRenderMethod($view);
			if (!PresenterHelpers::isMethodCallable($presenterClass, $method)) {
				$method = NULL;
			}
		}
		if ($method !== NULL) {
			foreach (PresenterHelpers::getMethodParams($presenterClass, $method) as $name => $def) {
				if (isset($this->params[$name])) {
					$params[$name] = $this->params[$name];
				}
			}
		}

		if ($componentId === '') { // self
			if ($signal != NULL) { // intentionally ==
				$params[self::SIGNAL_KEY] = strtolower($signal);
			}
			$params = $cparams + $params;

		} elseif ($componentId !== NULL) {
			$prefix = $componentId . self::NAME_SEPARATOR;
			if ($signal != NULL) { // intentionally ==
				$params[self::SIGNAL_KEY] = $prefix . strtolower($signal);
			}
			foreach ($cparams as $key => $val) {
				$params[$prefix . $key] = $val;
			}
		}

		$this->saveGlobalState();
		$this->saveState($params, $presenterClass);
		$params += $this->globalState[NULL];
		$params[self::VIEW_KEY] = $view;

		$request = new PresenterRequest(
			$this->getName(),
			PresenterRequest::FORWARD,
			$params
		);

		return $returnUri ? $this->constructUrl($request) : $request;
	}



	/**
	 * Constructs URL or throws exception.
	 * @param  PresenterRequest
	 * @return string
	 * @throws InvalidLinkException
	 */
	protected function constructUrl($request)
	{
		$uri = $this->router->constructUrl($request, $this->httpRequest);
		if ($uri === NULL) {
			$presenter = $request->getPresenterName();
			$params = $request->params;
			$view = $params['view'];
			unset($params['view']);
			$params = urldecode(http_build_query($params, NULL, ', '));
			throw new InvalidLinkException("No route for $presenter:$view($params)");
		}
		//return $this->getAjaxDriver()->link($uri);
		return $uri;
	}



	/********************* interface IStatePersistent ****************d*g**/



	/**
	 * Saves state informations for next request.
	 * @param  array
	 * @param  portion specified by class name (used by Presenter)
	 * @return void
	 */
	public function saveState(array & $params, $forClass = NULL)
	{
		foreach (PresenterHelpers::getPersistentParams($forClass) as $nm => $l)
		{
			if (isset($params[$nm])) {
				$val = $params[$nm]; // injected value

			} elseif (array_key_exists($nm, $params)) {
				continue; // i.e. $params[$nm] === NULL -> means skip

			} elseif ($this instanceof $l['since']) {
				$val = $this->$nm; // object property value

			} else {
				continue; // foreign parameter which is not set
			}

			if (is_object($val)) {
				if ($val instanceof IStatePersistent) { // and $state
					$nm = $val->getUniqueId();
					if (isset($this->globalState[$nm])) {
						$params += $this->globalState[$nm];
					}
				} else {
					throw new InvalidStateException("Persistent parameter must be scalar, array or IStatePersistent, '$this->class::\$$nm' is " . gettype($val));
				}

			} else {
				if ($l['type'] === NULL) {
					if ((string) $val === '') $val = NULL; // unnecessary
				} else {
					settype($val, $l['type']);
					if ($val === $l['def']) $val = NULL;
				}
				$params[$nm] = $val;
			}
		}
	}



	/**
	 * Saves state information for all subcomponents to $this->globalState.
	 * @return void
	 */
	protected function saveGlobalState()
	{
		if ($this->globalState === NULL || $this->phase <= self::PHASE_SIGNAL) {
			$state = array();
			foreach ($this->globalComponents as $id => $component)
			{
				if ($component instanceof IStatePersistent) {
					$params = array();
					$component->saveState($params);
					if ($id === '') { // presenter itself
						$state[NULL] = $params;
					} else {
						$prefix = $id . self::NAME_SEPARATOR;
						$prefix2 = substr($prefix, 0, strpos($prefix, self::NAME_SEPARATOR));
						foreach ($params as $key => $val) {
							$state[NULL][$prefix . $key] = $val;
							$state[$prefix2][$prefix . $key] = $val;
						}
					}
				}
			}
			$this->globalState = $state;
		}
	}



	/**
	 * Initializes $this->globalParams, $this->signal & $this->signalReceiver, $this->view, $this->scene.
	 * @return void
	 * @throws BadRequestException if view name is not valid
	 */
	private function initGlobalParams()
	{
		// init $this->globalParams
		$self = $this->getUniqueId();
		$this->globalParams = array();
		$selfParams = & $this->globalParams[$self];
		$selfParams = array();

		foreach ($this->request->getParams() as $key => $value) {
			$a = strlen($key) > 2 ? strrpos($key, self::NAME_SEPARATOR, -2) : FALSE;
			if ($a === FALSE) {
				$selfParams[$key] = $value;
			} else {
				$this->globalParams[substr($key, 0, $a)][substr($key, $a + 1)] = $value;
			}
		}

		// init & validate $this->view & $this->scene
		$this->changeView(isset($selfParams[self::VIEW_KEY]) ? $selfParams[self::VIEW_KEY] : self::$defaultView);

		// init $this->signalReceiver and key 'signal' in appropriate params array
		$this->signalReceiver = $self;
		if (!empty($selfParams[self::SIGNAL_KEY])) {
			$param = $selfParams[self::SIGNAL_KEY];
			$pos = strrpos($param, '-');
			if ($pos) {
				$this->signalReceiver = substr($param, 0, $pos);
				$this->signal = substr($param, $pos + 1);
			} else {
				$this->signalReceiver = $self;
				$this->signal = $param;
			}
			if ($this->signal == NULL) { // intentionally ==
				$this->signal = NULL;
			}
		}
	}



	/********************* hierarchy tree ****************d*g**/



	/**
	 * @param  string
	 * @param  Nette::IComponent
	 * @return bool
	 */
	final public function registerComponent($id, /*Nette::*/IComponent $component)
	{
		if (isset($this->globalComponents[$id])) {
			return FALSE;
		}

		$this->globalComponents[$id] = $component;
		if (isset($this->globalParams[$id]) && $component instanceof IStatePersistent) {
			$component->loadState($this->globalParams[$id]);
		}

		return TRUE;
	}



	/**
	 * @param  Nette::IComponent
	 * @return bool
	 */
	final public function unregisterComponent(/*Nette::*/IComponent $component)
	{
		foreach ($this->globalComponents as $id => $c) {
			if ($c === $component) {
				unset($this->globalComponents[$id]);
				return TRUE;
			}
		}
		return FALSE;
	}

}
