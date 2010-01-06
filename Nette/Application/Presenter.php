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
 * Presenter object represents a webpage instance. It executes all the logic for the request.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Application
 *
 * @property-read PresenterRequest $request
 * @property-read int $phase
 * @property-read array $signal
 * @property-read string $action
 * @property   string $view
 * @property   string $layout
 * @property-read mixed $payload
 * @property-read Application $application
 */
abstract class Presenter extends Control implements IPresenter
{
	/**#@+ @deprecated */
	const PHASE_STARTUP = 1;
	const PHASE_SIGNAL = 3;
	const PHASE_RENDER = 4;
	const PHASE_SHUTDOWN = 5;
	/**#@-*/

	/**#@+ bad link handling {@link Presenter::$invalidLinkMode} */
	const INVALID_LINK_SILENT = 1;
	const INVALID_LINK_WARNING = 2;
	const INVALID_LINK_EXCEPTION = 3;
	/**#@-*/

	/**#@+ @internal special parameter key */
	const SIGNAL_KEY = 'do';
	const ACTION_KEY = 'action';
	const FLASH_KEY = '_fid';
	/**#@-*/

	/** @var string */
	public static $defaultAction = 'default';

	/** @var int */
	public static $invalidLinkMode;

	/** @var array of function(Presenter $sender, IPresenterResponse $response = NULL); Occurs when the presenter is shutting down */
	public $onShutdown;

	/** @var bool (experimental) */
	public $oldLayoutMode = TRUE;

	/** @var bool (experimental) */
	public $oldModuleMode = TRUE;

	/** @var PresenterRequest */
	private $request;

	/** @var IPresenterResponse */
	private $response;

	/** @var int */
	private $phase;

	/** @var bool  automatically call canonicalize() */
	public $autoCanonicalize = TRUE;

	/** @var bool  use absolute Urls or paths? */
	public $absoluteUrls = FALSE;

	/** @var array */
	private $globalParams;

	/** @var array */
	private $globalState;

	/** @var array */
	private $globalStateSinces;

	/** @var string */
	private $action;

	/** @var string */
	private $view;

	/** @var string */
	private $layout;

	/** @var stdClass */
	private $payload;

	/** @var string */
	private $signalReceiver;

	/** @var string */
	private $signal;

	/** @var bool */
	private $ajaxMode;

	/** @var bool */
	private $startupCheck;

	/** @var PresenterRequest */
	private $lastCreatedRequest;

	/** @var array */
	private $lastCreatedRequestFlag;



	/**
	 * @return PresenterRequest
	 */
	final public function getRequest()
	{
		return $this->request;
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



	/********************* interface IPresenter ****************d*g**/



	/**
	 * @param  PresenterRequest
	 * @return IPresenterResponse
	 */
	public function run(PresenterRequest $request)
	{
		try {
			// PHASE 1: STARTUP
			$this->phase = self::PHASE_STARTUP;
			$this->request = $request;
			$this->payload = (object) NULL;
			$this->setParent($this->getParent(), $request->getPresenterName());

			$this->initGlobalParams();
			$this->startup();
			if (!$this->startupCheck) {
				$class = $this->reflection->getMethod('startup')->getDeclaringClass()->getName();
				trigger_error("Method $class::startup() or its descendant doesn't call parent::startup().", E_USER_WARNING);
			}
			// calls $this->action<Action>()
			$this->tryCall($this->formatActionMethod($this->getAction()), $this->params);

			if ($this->autoCanonicalize) {
				$this->canonicalize();
			}
			if ($this->getHttpRequest()->isMethod('head')) {
				$this->terminate();
			}

			// back compatibility
			if (method_exists($this, 'beforePrepare')) {
				$this->beforePrepare();
				trigger_error('beforePrepare() is deprecated; use createComponent{Name}() instead.', E_USER_WARNING);
			}
			if ($this->tryCall('prepare' . $this->getView(), $this->params)) {
				trigger_error('prepare' . ucfirst($this->getView()) . '() is deprecated; use createComponent{Name}() instead.', E_USER_WARNING);
			}

			// PHASE 2: SIGNAL HANDLING
			$this->phase = self::PHASE_SIGNAL;
			// calls $this->handle<Signal>()
			$this->processSignal();

			// PHASE 3: RENDERING VIEW
			$this->phase = self::PHASE_RENDER;

			$this->beforeRender();
			// calls $this->render<View>()
			$this->tryCall($this->formatRenderMethod($this->getView()), $this->params);
			$this->afterRender();

			// save component tree persistent state
			$this->saveGlobalState();
			if ($this->isAjax()) {
				$this->payload->state = $this->getGlobalState();
			}

			// finish template rendering
			$this->sendTemplate();

		} catch (AbortException $e) {
			// continue with shutting down
		} /* finally */ {

			// PHASE 4: SHUTDOWN
			$this->phase = self::PHASE_SHUTDOWN;

			if ($this->isAjax()) try {
				$hasPayload = (array) $this->payload; unset($hasPayload['state']);
				if ($this->response instanceof RenderResponse && ($this->isControlInvalid() || $hasPayload)) { // snippets - TODO
					/*Nette\Templates\*/SnippetHelper::$outputAllowed = FALSE;
					$this->response->send();
					$this->sendPayload();

				} elseif (!$this->response && $hasPayload) { // back compatibility for use terminate() instead of sendPayload()
					$this->sendPayload();
				}
			} catch (AbortException $e) { }

			if ($this->hasFlashSession()) {
				$this->getFlashSession()->setExpiration($this->response instanceof RedirectingResponse ? '+ 30 seconds': '+ 3 seconds');
			}

			$this->onShutdown($this, $this->response);
			$this->shutdown($this->response);

			return $this->response;
		}
	}



	/**
	 * @deprecated
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
		$this->startupCheck = TRUE;
	}



	/**
	 * Common render method.
	 * @return void
	 */
	protected function beforeRender()
	{
	}



	/**
	 * Common render method.
	 * @return void
	 */
	protected function afterRender()
	{
	}



	/**
	 * @param  IPresenterResponse  optional catched exception
	 * @return void
	 */
	protected function shutdown($response)
	{
	}



	/********************* signal handling ****************d*g**/



	/**
	 * @return void
	 * @throws BadSignalException
	 */
	public function processSignal()
	{
		if ($this->signal === NULL) return;

		$component = $this->signalReceiver === '' ? $this : $this->getComponent($this->signalReceiver, FALSE);
		if ($component === NULL) {
			throw new BadSignalException("The signal receiver component '$this->signalReceiver' is not found.");

		} elseif (!$component instanceof ISignalReceiver) {
			throw new BadSignalException("The signal receiver component '$this->signalReceiver' is not ISignalReceiver implementor.");
		}

		// auto invalidate
		if ($this->oldLayoutMode && $component instanceof IRenderable) {
			$component->invalidateControl();
		}

		$component->signalReceived($this->signal);
		$this->signal = NULL;
	}



	/**
	 * Returns pair signal receiver and name.
	 * @return array|NULL
	 */
	final public function getSignal()
	{
		return $this->signal === NULL ? NULL : array($this->signalReceiver, $this->signal);
	}



	/**
	 * Checks if the signal receiver is the given one.
	 * @param  mixed  component or its id
	 * @param  string signal name (optional)
	 * @return bool
	 */
	final public function isSignalReceiver($component, $signal = NULL)
	{
		if ($component instanceof /*Nette\*/Component) {
			$component = $component === $this ? '' : $component->lookupPath(__CLASS__, TRUE);
		}

		if ($this->signal === NULL) {
			return FALSE;

		} elseif ($signal === TRUE) {
			return $component === ''
				|| strncmp($this->signalReceiver . '-', $component . '-', strlen($component) + 1) === 0;

		} elseif ($signal === NULL) {
			return $this->signalReceiver === $component;

		} else {
			return $this->signalReceiver === $component && strcasecmp($signal, $this->signal) === 0;
		}
	}



	/********************* rendering ****************d*g**/



	/**
	 * Returns current action name.
	 * @return string
	 */
	final public function getAction($fullyQualified = FALSE)
	{
		return $fullyQualified ? ':' . $this->getName() . ':' . $this->action : $this->action;
	}



	/**
	 * Changes current action. Only alphanumeric characters are allowed.
	 * @param  string
	 * @return void
	 */
	public function changeAction($action)
	{
		if (preg_match("#^[a-zA-Z0-9][a-zA-Z0-9_\x7f-\xff]*$#", $action)) {
			$this->action = $action;
			$this->view = $action;

		} else {
			throw new BadRequestException("Action name '$action' is not alphanumeric string.");
		}
	}



	/**
	 * Returns current view.
	 * @return string
	 */
	final public function getView()
	{
		return $this->view;
	}



	/**
	 * Changes current view. Any name is allowed.
	 * @param  string
	 * @return Presenter  provides a fluent interface
	 */
	public function setView($view)
	{
		$this->view = (string) $view;
		return $this;
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
	 * @return Presenter  provides a fluent interface
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout === FALSE ? FALSE : (string) $layout;
		return $this;
	}



	/**
	 * @return void
	 * @throws BadRequestException if no template found
	 * @throws AbortException
	 */
	public function sendTemplate()
	{
		$template = $this->getTemplate();
		if (!$template) return;

		if ($template instanceof /*Nette\Templates\*/IFileTemplate && !$template->getFile()) {

			// content template
			$files = $this->formatTemplateFiles($this->getName(), $this->view);
			foreach ($files as $file) {
				if (is_file($file)) {
					$template->setFile($file);
					break;
				}
			}

			if (!$template->getFile()) {
				$file = str_replace(Environment::getVariable('appDir'), "\xE2\x80\xA6", reset($files));
				throw new BadRequestException("Page not found. Missing template '$file'.");
			}

			// layout template
			if ($this->layout !== FALSE) {
				$files = $this->formatLayoutTemplateFiles($this->getName(), $this->layout ? $this->layout : 'layout');
				foreach ($files as $file) {
					if (is_file($file)) {
						$template->layout = $file;
						if ($this->oldLayoutMode) {
							$template->content = clone $template;
							$template->setFile($file);
						} else {
							$template->_extends = $file;
						}
						break;
					}
				}

				if (empty($template->layout) && $this->layout !== NULL) {
					$file = str_replace(Environment::getVariable('appDir'), "\xE2\x80\xA6", reset($files));
					throw new /*\*/FileNotFoundException("Layout not found. Missing template '$file'.");
				}
			}
		}

		$this->terminate(new RenderResponse($template));
	}



	/**
	 * Formats layout template file names.
	 * @param  string
	 * @param  string
	 * @return array
	 */
	public function formatLayoutTemplateFiles($presenter, $layout)
	{
		if ($this->oldModuleMode) {
			$root = Environment::getVariable('templatesDir', Environment::getVariable('appDir') . '/templates'); // back compatibility
			$presenter = str_replace(':', 'Module/', $presenter);
			$module = substr($presenter, 0, (int) strrpos($presenter, '/'));
			$base = '';
			if ($root === Environment::getVariable('appDir') . '/presenters') {
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

		$appDir = Environment::getVariable('appDir');
		$path = '/' . str_replace(':', 'Module/', $presenter);
		$pathP = substr_replace($path, '/templates', strrpos($path, '/'), 0);
		$list = array(
			"$appDir$pathP/@$layout.phtml",
			"$appDir$pathP.@$layout.phtml",
		);
		while (($path = substr($path, 0, strrpos($path, '/'))) !== FALSE) {
			$list[] = "$appDir$path/templates/@$layout.phtml";
		}
		return $list;
	}



	/**
	 * Formats view template file names.
	 * @param  string
	 * @param  string
	 * @return array
	 */
	public function formatTemplateFiles($presenter, $view)
	{
		if ($this->oldModuleMode) {
			$root = Environment::getVariable('templatesDir', Environment::getVariable('appDir') . '/templates'); // back compatibility
			$presenter = str_replace(':', 'Module/', $presenter);
			$dir = '';
			if ($root === Environment::getVariable('appDir') . '/presenters') { // special supported case
				$pos = strrpos($presenter, '/');
				$presenter = $pos === FALSE ? 'templates/' . $presenter : substr_replace($presenter, '/templates', $pos, 0);
				$dir = 'templates/';
			}
			return array(
				"$root/$presenter/$view.phtml",
				"$root/$presenter.$view.phtml",
				"$root/$dir@global.$view.phtml",
			);
		}

		$appDir = Environment::getVariable('appDir');
		$path = '/' . str_replace(':', 'Module/', $presenter);
		$pathP = substr_replace($path, '/templates', strrpos($path, '/'), 0);
		$path = substr_replace($path, '/templates', strrpos($path, '/'));
		return array(
			"$appDir$pathP/$view.phtml",
			"$appDir$pathP.$view.phtml",
			"$appDir$path/@global.$view.phtml",
		);
	}



	/**
	 * Formats action method name.
	 * @param  string
	 * @return string
	 */
	protected static function formatActionMethod($action)
	{
		return 'action' . $action;
	}



	/**
	 * Formats render view method name.
	 * @param  string
	 * @return string
	 */
	protected static function formatRenderMethod($view)
	{
		return 'render' . $view;
	}



	/**
	 * @deprecated
	 */
	protected function renderTemplate()
	{
		throw new /*\*/DeprecatedException(__METHOD__ . '() is deprecated; use $presenter->sendTemplate() instead.');
	}



	/********************* partial AJAX rendering ****************d*g**/



	/**
	 * @return stdClass
	 */
	final public function getPayload()
	{
		return $this->payload;
	}



	/**
	 * Is AJAX request?
	 * @return bool
	 */
	public function isAjax()
	{
		if ($this->ajaxMode === NULL) {
			$this->ajaxMode = $this->getHttpRequest()->isAjax();
		}
		return $this->ajaxMode;
	}



	/**
	 * Sends AJAX payload to the output.
	 * @return void
	 * @throws AbortException
	 */
	protected function sendPayload()
	{
		$this->terminate(new JsonResponse($this->payload));
	}



	/**
	 * @deprecated
	 */
	public function getAjaxDriver()
	{
		throw new /*\*/DeprecatedException(__METHOD__ . '() is deprecated; use $presenter->payload instead.');
	}



	/********************* navigation & flow ****************d*g**/



	/**
	 * Forward to another presenter or action.
	 * @param  string|PresenterRequest
	 * @param  array|mixed
	 * @return void
	 * @throws AbortException
	 */
	public function forward($destination, $args = array())
	{
		if ($destination instanceof PresenterRequest) {
			$this->terminate(new ForwardingResponse($destination));

		} elseif (!is_array($args)) {
			$args = func_get_args();
			array_shift($args);
		}

		$this->createRequest($this, $destination, $args, 'forward');
		$this->terminate(new ForwardingResponse($this->lastCreatedRequest));
	}



	/**
	 * Redirect to another URL and ends presenter execution.
	 * @param  string
	 * @param  int HTTP error code
	 * @return void
	 * @throws AbortException
	 */
	public function redirectUri($uri, $code = NULL)
	{
		if ($this->isAjax()) {
			$this->payload->redirect = (string) $uri;
			$this->sendPayload();

		} elseif (!$code) {
			$code = $this->getHttpRequest()->isMethod('post') ? /*Nette\Web\*/IHttpResponse::S303_POST_GET : /*Nette\Web\*/IHttpResponse::S302_FOUND;
		}
		$this->terminate(new RedirectingResponse($uri, $code));
	}



	/**
	 * Link to myself.
	 * @return string
	 */
	public function backlink()
	{
		return $this->getAction(TRUE);
	}



	/**
	 * Returns the last created PresenterRequest.
	 * @return PresenterRequest
	 */
	public function getLastCreatedRequest()
	{
		return $this->lastCreatedRequest;
	}



	/**
	 * Returns the last created PresenterRequest flag.
	 * @param  string
	 * @return bool
	 */
	public function getLastCreatedRequestFlag($flag)
	{
		return !empty($this->lastCreatedRequestFlag[$flag]);
	}



	/**
	 * Correctly terminates presenter.
	 * @param  IPresenterResponse
	 * @return void
	 * @throws AbortException
	 */
	public function terminate(IPresenterResponse $response = NULL)
	{
		$this->response = $response;
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
			$uri = $this->createRequest($this, $this->action, $this->getGlobalState() + $this->request->params, 'redirectX');
			if ($uri !== NULL && !$this->getHttpRequest()->getUri()->isEqual($uri)) {
				$this->terminate(new RedirectingResponse($uri, /*Nette\Web\*/IHttpResponse::S301_MOVED_PERMANENTLY));
			}
		}
	}



	/**
	 * Attempts to cache the sent entity by its last modification date
	 * @param  string|int|DateTime  last modified time
	 * @param  string strong entity tag validator
	 * @param  mixed  optional expiration time
	 * @return void
	 * @throws AbortException
	 * @deprecated
	 */
	public function lastModified($lastModified, $etag = NULL, $expire = NULL)
	{
		if (!Environment::isProduction()) {
			return;
		}

		if ($expire !== NULL) {
			$this->getHttpResponse()->setExpiration($expire);
		}

		if (!$this->getHttpContext()->isModified($lastModified, $etag)) {
			$this->terminate();
		}
	}



	/**
	 * PresenterRequest/URL factory.
	 * @param  PresenterComponent  base
	 * @param  string   destination in format "[[module:]presenter:]action" or "signal!"
	 * @param  array    array of arguments
	 * @param  string   forward|redirect|link
	 * @return string   URL
	 * @throws InvalidLinkException
	 * @internal
	 */
	final protected function createRequest($component, $destination, array $args, $mode)
	{
		// note: createRequest supposes that saveState(), run() & tryCall() behaviour is final

		// cached services for better performance
		static $presenterLoader, $router, $httpRequest;
		if ($presenterLoader === NULL) {
			$presenterLoader = $this->getApplication()->getPresenterLoader();
			$router = $this->getApplication()->getRouter();
			$httpRequest = $this->getHttpRequest();
		}

		$this->lastCreatedRequest = $this->lastCreatedRequestFlag = NULL;

		// PARSE DESTINATION
		// 1) fragment
		$a = strpos($destination, '#');
		if ($a === FALSE) {
			$fragment = '';
		} else {
			$fragment = substr($destination, $a);
			$destination = substr($destination, 0, $a);
		}

		// 2) ?query syntax
		$a = strpos($destination, '?');
		if ($a !== FALSE) {
			parse_str(substr($destination, $a + 1), $args); // requires disabled magic quotes
			$destination = substr($destination, 0, $a);
		}

		// 3) URL scheme
		$a = strpos($destination, '//');
		if ($a === FALSE) {
			$scheme = FALSE;
		} else {
			$scheme = substr($destination, 0, $a);
			$destination = substr($destination, $a + 2);
		}

		// 4) signal or empty
		if (!($component instanceof Presenter) || substr($destination, -1) === '!') {
			$signal = rtrim($destination, '!');
			$a = strrpos($signal, ':');
			if ($a !== FALSE) {
				$component = $component->getComponent(strtr(substr($signal, 0, $a), ':', '-'));
				$signal = (string) substr($signal, $a + 1);
			}
			if ($signal == NULL) {  // intentionally ==
				throw new InvalidLinkException("Signal must be non-empty string.");
			}
			$destination = 'this';
		}

		if ($destination == NULL) {  // intentionally ==
			throw new InvalidLinkException("Destination must be non-empty string.");
		}

		// 5) presenter: action
		$current = FALSE;
		$a = strrpos($destination, ':');
		if ($a === FALSE) {
			$action = $destination === 'this' ? $this->action : $destination;
			$presenter = $this->getName();
			$presenterClass = get_class($this);

		} else {
			$action = (string) substr($destination, $a + 1);
			if ($destination[0] === ':') { // absolute
				if ($a < 2) {
					throw new InvalidLinkException("Missing presenter name in '$destination'.");
				}
				$presenter = substr($destination, 1, $a - 1);

			} else { // relative
				$presenter = $this->getName();
				$b = strrpos($presenter, ':');
				if ($b === FALSE) { // no module
					$presenter = substr($destination, 0, $a);
				} else { // with module
					$presenter = substr($presenter, 0, $b + 1) . substr($destination, 0, $a);
				}
			}
			$presenterClass = $presenterLoader->getPresenterClass($presenter);
		}

		// PROCESS SIGNAL ARGUMENTS
		if (isset($signal)) { // $component must be IStatePersistent
			$reflection = new PresenterComponentReflection(get_class($component));
			if ($signal === 'this') { // means "no signal"
				$signal = '';
				if (array_key_exists(0, $args)) {
					throw new InvalidLinkException("Extra parameter for signal '{$reflection->name}:$signal!'.");
				}

			} elseif (strpos($signal, self::NAME_SEPARATOR) === FALSE) { // TODO: AppForm exception
				// counterpart of signalReceived() & tryCall()
				$method = $component->formatSignalMethod($signal);
				if (!$reflection->hasCallableMethod($method)) {
					throw new InvalidLinkException("Unknown signal '{$reflection->name}:$signal!'.");
				}
				if ($args) { // convert indexed parameters to named
					self::argsToParams(get_class($component), $method, $args);
				}
			}

			// counterpart of IStatePersistent
			if ($args && array_intersect_key($args, $reflection->getPersistentParams())) {
				$component->saveState($args);
			}

			if ($args && $component !== $this) {
				$prefix = $component->getUniqueId() . self::NAME_SEPARATOR;
				foreach ($args as $key => $val) {
					unset($args[$key]);
					$args[$prefix . $key] = $val;
				}
			}
		}

		// PROCESS ARGUMENTS
		if (is_subclass_of($presenterClass, __CLASS__)) {
			if ($action === '') {
				/*$action = $presenterClass::$defaultAction;*/ // in PHP 5.3
				/**/$action = self::$defaultAction;/**/
			}

			$current = ($action === '*' || $action === $this->action) && $presenterClass === get_class($this); // TODO

			$reflection = new PresenterComponentReflection($presenterClass);
			if ($args || $destination === 'this') {
				// counterpart of run() & tryCall()
				/*$method = $presenterClass::formatActionMethod($action);*/ // in PHP 5.3
				/**/$method = call_user_func(array($presenterClass, 'formatActionMethod'), $action);/**/
				if (!$reflection->hasCallableMethod($method)) {
					/*$method = $presenterClass::formatRenderMethod($action);*/ // in PHP 5.3
					/**/$method = call_user_func(array($presenterClass, 'formatRenderMethod'), $action);/**/
					if (!$reflection->hasCallableMethod($method)) {
						$method = NULL;
					}
				}

				// convert indexed parameters to named
				if ($method === NULL) {
					if (array_key_exists(0, $args)) {
						throw new InvalidLinkException("Extra parameter for '$presenter:$action'.");
					}

				} elseif ($destination === 'this') {
					self::argsToParams($presenterClass, $method, $args, $this->params);

				} else {
					self::argsToParams($presenterClass, $method, $args);
				}
			}

			// counterpart of IStatePersistent
			if ($args && array_intersect_key($args, $reflection->getPersistentParams())) {
				$this->saveState($args, $reflection);
			}

			$globalState = $this->getGlobalState($destination === 'this' ? NULL : $presenterClass);
			if ($current && $args) {
				$tmp = $globalState + $this->params;
				foreach ($args as $key => $val) {
					if ((string) $val !== (isset($tmp[$key]) ? (string) $tmp[$key] : '')) {
						$current = FALSE;
						break;
					}
				}
			}
			$args += $globalState;
		}

		// ADD ACTION & SIGNAL & FLASH
		$args[self::ACTION_KEY] = $action;
		if (!empty($signal)) {
			$args[self::SIGNAL_KEY] = $component->getParamId($signal);
			$current = $current && $args[self::SIGNAL_KEY] === $this->getParam(self::SIGNAL_KEY);
		}
		if (($mode === 'redirect' || $mode === 'forward') && $this->hasFlashSession()) {
			$args[self::FLASH_KEY] = $this->getParam(self::FLASH_KEY);
		}

		$this->lastCreatedRequest = new PresenterRequest(
			$presenter,
			PresenterRequest::FORWARD,
			$args,
			array(),
			array()
		);
		$this->lastCreatedRequestFlag = array('current' => $current);

		if ($mode === 'forward') return;

		// CONSTRUCT URL
		$uri = $router->constructUrl($this->lastCreatedRequest, $httpRequest);
		if ($uri === NULL) {
			unset($args[self::ACTION_KEY]);
			$params = urldecode(http_build_query($args, NULL, ', '));
			throw new InvalidLinkException("No route for $presenter:$action($params)");
		}

		// make URL relative if possible
		if ($mode === 'link' && $scheme === FALSE && !$this->absoluteUrls) {
			$hostUri = $httpRequest->getUri()->getHostUri();
			if (strncmp($uri, $hostUri, strlen($hostUri)) === 0) {
				$uri = substr($uri, strlen($hostUri));
			}
		}

		return $uri . $fragment;
	}



	/**
	 * Converts list of arguments to named parameters.
	 * @param  string  class name
	 * @param  string  method name
	 * @param  array   arguments
	 * @param  array   supplemental arguments
	 * @return void
	 * @throws InvalidLinkException
	 */
	private static function argsToParams($class, $method, & $args, $supplemental = array())
	{
		static $cache;
		$params = & $cache[strtolower($class . ':' . $method)];
		if ($params === NULL) {
			$params = /*Nette\Reflection\*/MethodReflection::from($class, $method)->getDefaultParameters();
		}
		$i = 0;
		foreach ($params as $name => $def) {
			if (array_key_exists($i, $args)) {
				$args[$name] = $args[$i];
				unset($args[$i]);
				$i++;

			} elseif (array_key_exists($name, $args)) {
				// continue with process

			} elseif (array_key_exists($name, $supplemental)) {
				$args[$name] = $supplemental[$name];

			} else {
				continue;
			}

			if ($def === NULL) {
				if ((string) $args[$name] === '') $args[$name] = NULL; // value transmit is unnecessary
			} else {
				settype($args[$name], gettype($def));
				if ($args[$name] === $def) $args[$name] = NULL;
			}
		}

		if (array_key_exists($i, $args)) {
			throw new InvalidLinkException("Extra parameter for signal '$class:$method'.");
		}
	}



	/**
	 * Invalid link handler. Descendant can override this method to change default behaviour.
	 * @param  InvalidLinkException
	 * @return string
	 * @throws InvalidLinkException
	 */
	protected function handleInvalidLink($e)
	{
		if (self::$invalidLinkMode === NULL) {
			self::$invalidLinkMode = Environment::isProduction()
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



	/********************* interface IStatePersistent ****************d*g**/



	/**
	 * Returns array of persistent components.
	 * This default implementation detects components by class-level annotation @persistent(cmp1, cmp2).
	 * @return array
	 */
	public static function getPersistentComponents()
	{
		return (array) /*Nette\Reflection\*/ClassReflection::from(/**/func_get_arg(0)/**//*get_called_class()*/)->getAnnotation('persistent');
	}



	/**
	 * Saves state information for all subcomponents to $this->globalState.
	 * @return array
	 */
	private function getGlobalState($forClass = NULL)
	{
		$sinces = & $this->globalStateSinces;

		if ($this->globalState === NULL) {
			$state = array();
			foreach ($this->globalParams as $id => $params) {
				$prefix = $id . self::NAME_SEPARATOR;
				foreach ($params as $key => $val) {
					$state[$prefix . $key] = $val;
				}
			}
			$this->saveState($state, $forClass ? new PresenterComponentReflection($forClass) : NULL);

			if ($sinces === NULL) {
				$sinces = array();
				foreach ($this->getReflection()->getPersistentParams() as $nm => $meta) {
					$sinces[$nm] = $meta['since'];
				}
			}

			$components = $this->getReflection()->getPersistentComponents();
			$iterator = $this->getComponents(TRUE, 'Nette\Application\IStatePersistent');
			foreach ($iterator as $name => $component)
			{
				if ($iterator->getDepth() === 0) {
					// counts with RecursiveIteratorIterator::SELF_FIRST
					$since = isset($components[$name]['since']) ? $components[$name]['since'] : FALSE; // FALSE = nonpersistent
				}
				$prefix = $component->getUniqueId() . self::NAME_SEPARATOR;
				$params = array();
				$component->saveState($params);
				foreach ($params as $key => $val) {
					$state[$prefix . $key] = $val;
					$sinces[$prefix . $key] = $since;
				}
			}

		} else {
			$state = $this->globalState;
		}

		if ($forClass !== NULL) {
			$since = NULL;
			foreach ($state as $key => $foo) {
				if (!isset($sinces[$key])) {
					$x = strpos($key, self::NAME_SEPARATOR);
					$x = $x === FALSE ? $key : substr($key, 0, $x);
					$sinces[$key] = isset($sinces[$x]) ? $sinces[$x] : FALSE;
				}
				if ($since !== $sinces[$key]) {
					$since = $sinces[$key];
					$ok = $since && (is_subclass_of($forClass, $since) || $forClass === $since);
				}
				if (!$ok) {
					unset($state[$key]);
				}
			}
		}

		return $state;
	}



	/**
	 * Permanently saves state information for all subcomponents to $this->globalState.
	 * @return void
	 */
	protected function saveGlobalState()
	{
		// load lazy components
		foreach ($this->globalParams as $id => $foo) {
			$this->getComponent($id, FALSE);
		}

		$this->globalParams = array();
		$this->globalState = $this->getGlobalState();
	}



	/**
	 * Initializes $this->globalParams, $this->signal & $this->signalReceiver, $this->action, $this->view. Called by run().
	 * @return void
	 * @throws BadRequestException if action name is not valid
	 */
	private function initGlobalParams()
	{
		// init $this->globalParams
		$this->globalParams = array();
		$selfParams = array();

		$params = $this->request->getParams();
		if ($this->isAjax()) {
			$params = $this->request->getPost() + $params;
		}

		foreach ($params as $key => $value) {
			$a = strlen($key) > 2 ? strrpos($key, self::NAME_SEPARATOR, -2) : FALSE;
			if ($a === FALSE) {
				$selfParams[$key] = $value;
			} else {
				$this->globalParams[substr($key, 0, $a)][substr($key, $a + 1)] = $value;
			}
		}

		// init & validate $this->action & $this->view
		$this->changeAction(isset($selfParams[self::ACTION_KEY]) ? $selfParams[self::ACTION_KEY] : self::$defaultAction);

		// init $this->signalReceiver and key 'signal' in appropriate params array
		$this->signalReceiver = $this->getUniqueId();
		if (!empty($selfParams[self::SIGNAL_KEY])) {
			$param = $selfParams[self::SIGNAL_KEY];
			$pos = strrpos($param, '-');
			if ($pos) {
				$this->signalReceiver = substr($param, 0, $pos);
				$this->signal = substr($param, $pos + 1);
			} else {
				$this->signalReceiver = $this->getUniqueId();
				$this->signal = $param;
			}
			if ($this->signal == NULL) { // intentionally ==
				$this->signal = NULL;
			}
		}

		$this->loadState($selfParams);
	}



	/**
	 * Pops parameters for specified component.
	 * @param  string  component id
	 * @return array
	 */
	final public function popGlobalParams($id)
	{
		if (isset($this->globalParams[$id])) {
			$res = $this->globalParams[$id];
			unset($this->globalParams[$id]);
			return $res;

		} else {
			return array();
		}
	}



	/********************* flash session ****************d*g**/



	/**
	 * Checks if a flash session namespace exists.
	 * @return bool
	 */
	public function hasFlashSession()
	{
		return !empty($this->params[self::FLASH_KEY])
			&& $this->getSession()->hasNamespace('Nette.Application.Flash/' . $this->params[self::FLASH_KEY]);
	}



	/**
	 * Returns session namespace provided to pass temporary data between redirects.
	 * @return Nette\Web\SessionNamespace
	 */
	public function getFlashSession()
	{
		if (empty($this->params[self::FLASH_KEY])) {
			$this->params[self::FLASH_KEY] = substr(md5(lcg_value()), 0, 4);
		}
		return $this->getSession('Nette.Application.Flash/' . $this->params[self::FLASH_KEY]);
	}



	/********************* backend ****************d*g**/



	/**
	 * @return Nette\Web\HttpRequest
	 */
	protected function getHttpRequest()
	{
		return Environment::getHttpRequest();
	}



	/**
	 * @return Nette\Web\HttpResponse
	 */
	protected function getHttpResponse()
	{
		return Environment::getHttpResponse();
	}



	/**
	 * @return Nette\Web\HttpContext
	 */
	protected function getHttpContext()
	{
		return Environment::getHttpContext();
	}



	/**
	 * @return Application
	 */
	public function getApplication()
	{
		return Environment::getApplication();
	}



	/**
	 * @return Nette\Web\Session
	 */
	protected function getSession($namespace = NULL)
	{
		return Environment::getSession($namespace);
	}



	/**
	 * @return Nette\Web\User
	 */
	protected function getUser()
	{
		return Environment::getUser();
	}

}
