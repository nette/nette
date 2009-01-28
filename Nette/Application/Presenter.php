<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Application
 * @version    $Id$
 */

/*namespace Nette\Application;*/

/*use Nette\Environment;*/



require_once dirname(__FILE__) . '/../Application/Control.php';

require_once dirname(__FILE__) . '/../Application/IPresenter.php';



/**
 * Presenter object represents a webpage instance. It executes all the logic for the request.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Application
 */
abstract class Presenter extends Control implements IPresenter
{
	/**#@+ life cycle phases {@link Presenter::getPhase()} */
	const PHASE_STARTUP = 1;
	const PHASE_PREPARE = 2;
	const PHASE_SIGNAL = 3;
	const PHASE_RENDER = 4;
	const PHASE_SHUTDOWN = 5;
	/**#@-*/

	/**#@+ bad link handling {@link Presenter::$invalidLinkMode} */
	const INVALID_LINK_SILENT = 1;
	const INVALID_LINK_WARNING = 2;
	const INVALID_LINK_EXCEPTION = 3;
	/**#@-*/

	/**#@+ special parameter key */
	const SIGNAL_KEY = 'do';
	const VIEW_KEY = 'view';
	const FLASH_KEY = '_fid';
	/**#@-*/

	/** @var string */
	public static $defaultView = 'default';

	/** @var int */
	public static $invalidLinkMode;

	/** @var array of event handlers; Occurs when the presenter is shutting down; function(Presenter $sender, Exception $exception = NULL) */
	public $onShutdown;

	/** @var bool (experimental) */
	public $oldLayoutMode = TRUE;

	/** @var PresenterRequest */
	private $request;

	/** @var int */
	private $phase;

	/** @var bool  automatically call canonicalize() */
	public $autoCanonicalize = TRUE;

	/** @var bool  relativise links? */
	public $relativeLinks = TRUE;

	/** @var array */
	private $globalParams;

	/** @var array */
	private $globalState;

	/** @var array */
	private $globalStateSince;

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

	/** @var PresenterRequest */
	private $lastCreatedRequest;

	/** @var array */
	private $lastCreatedRequestFlag;



	/**
	 * @param  PresenterRequest
	 */
	public function __construct(PresenterRequest $request)
	{
		$this->request = $request;
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
				$this->getAjaxDriver()->open($this->getHttpResponse());
			}
			$this->initGlobalParams();
			$this->startup();
			// calls $this->present{view}();
			$this->tryCall($this->formatPresentMethod($this->getView()), $this->params);

			if ($this->autoCanonicalize) {
				$this->canonicalize();
			}
			if ($this->getHttpRequest()->isMethod('head')) {
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

			// PHASE 4: RENDERING SCENE
			$this->phase = self::PHASE_RENDER;

			$this->beforeRender();
			// calls $this->render{scene}();
			$this->tryCall($this->formatRenderMethod($this->getScene()), $this->params);
			$this->afterRender();

			// save component tree persistent state
			$this->saveGlobalState();
			if ($this->isAjax()) {
				$this->ajaxDriver->state = $this->getGlobalState();
			}

			// finish template rendering
			$this->renderTemplate();

			$e = NULL;

		} catch (AbortException $e) {
			// continue with shutting down
		} /* finally */ {

			// PHASE 5: SHUTDOWN
			$this->phase = self::PHASE_SHUTDOWN;

			if ($this->isAjax()) {
				$this->ajaxDriver->close();
			}

			if ($this->hasFlashSession()) {
				$this->getFlashSession()->setExpiration($e instanceof RedirectingException ? 30 : 3);
			}

			$this->onShutdown($this, $e);
			$this->shutdown($e);

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
	 * Common render method.
	 * @return void
	 */
	protected function afterRender()
	{
	}



	/**
	 * @param  Exception  optional catched exception
	 * @return void
	 */
	protected function shutdown($exception)
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
		if ($component instanceof IRenderable) {
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
	 * Returns current view name.
	 * @return string
	 */
	final public function getView($fullyQualified = FALSE)
	{
		return $fullyQualified ? ':' . $this->getName() . ':' . $this->view : $this->view;
	}



	/**
	 * Changes current view. Only alphanumeric characters are allowed.
	 * @param  string
	 * @return void
	 */
	public function changeView($view)
	{
		if (preg_match("#^[a-zA-Z0-9][a-zA-Z0-9_\x7f-\xff]*$#", $view)) {
			$this->view = $view;
			$this->scene = $view;

		} else {
			throw new BadRequestException("View name '$view' is not alphanumeric string.");
		}
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
	 * Changes current view scene. Any name is allowed.
	 * @param  string
	 * @return void
	 */
	public function changeScene($scene)
	{
		$this->scene = (string) $scene;
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
		$this->layout = (string) $layout;
	}



	/**
	 * @return void
	 * @throws BadRequestException if no template found
	 */
	public function renderTemplate()
	{
		$template = $this->getTemplate();
		if (!$template) return;

		if ($this->isAjax()) { // TODO!
			SnippetHelper::$outputAllowed = FALSE;
		}

		if ($template instanceof /*Nette\Templates\*/IFileTemplate && !$template->getFile()) {

			if (isset($template->layout)) {
				trigger_error('Parameter $template->layout is about to be reserved.', E_USER_WARNING);
			}

			unset($template->layout, $template->content);

			// content template
			$files = $this->formatTemplateFiles($this->getName(), $this->scene);
			foreach ($files as $file) {
				if (is_file($file)) {
					$template->setFile($file);
					break;
				}
			}

			if (!$template->getFile()) {
				$file = reset($files);
				throw new BadRequestException("Page not found. Missing template '$file'.");
			}

			// layout template
			if ($this->layout) {
				foreach ($this->formatLayoutTemplateFiles($this->getName(), $this->layout) as $file) {
					if (is_file($file)) {
						if ($this->oldLayoutMode) {
							$template->content = $template instanceof /*Nette\Templates\*/Template ? $template->subTemplate($template->getFile()) : $template->getFile();
							$template->setFile($file);
						} else {
							$template->layout = $file; // experimental
						}
						break;
					}
				}
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
	public function formatLayoutTemplateFiles($presenter, $layout)
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
	public function formatTemplateFiles($presenter, $scene)
	{
		$root = Environment::getVariable('templatesDir');
		$presenter = str_replace(':', 'Module/', $presenter);
		$dir = '';
		if ($root === Environment::getVariable('presentersDir')) { // special supported case
			$pos = strrpos($presenter, '/');
			$presenter = $pos === FALSE ? 'templates/' . $presenter : substr_replace($presenter, '/templates', $pos, 0);
			$dir = 'templates/';
		}
		return array(
			"$root/$presenter/$scene.phtml",
			"$root/$presenter.$scene.phtml",
			"$root/$dir@global.$scene.phtml",
		);
	}



	/**
	 * Formats execute method name.
	 * @param  string
	 * @return string
	 */
	protected static function formatPresentMethod($view)
	{
		return 'present' . $view;
	}



	/**
	 * Formats prepare scene method name.
	 * @param  string
	 * @return string
	 */
	protected static function formatPrepareMethod($scene)
	{
		return 'prepare' . $scene;
	}



	/**
	 * Formats render scene method name.
	 * @param  string
	 * @return string
	 */
	protected static function formatRenderMethod($scene)
	{
		return 'render' . $scene;
	}



	/********************* partial AJAX rendering ****************d*g**/



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
	 * @return IAjaxDriver|NULL
	 */
	public function getAjaxDriver()
	{
		if ($this->ajaxDriver === NULL) {
			$value = $this->createAjaxDriver();
			if (!($value instanceof IAjaxDriver)) {
				$class = get_class($value);
				throw new /*\*/UnexpectedValueException("Object returned by $this->class::getAjaxDriver() must be instance of Nette\\Application\\IAjaxDriver, '$class' given.");
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

		$this->createRequest($this, $destination, $args, 'forward');
		throw new ForwardingException($this->lastCreatedRequest);
	}



	/**
	 * Redirect to another URL and ends presenter execution.
	 * @param  string
	 * @param  int HTTP error code
	 * @return void
	 * @throws RedirectingException
	 */
	public function redirectUri($uri, $code = /*Nette\Web\*/IHttpResponse::S303_POST_GET)
	{
		if ($this->isAjax()) {
			$this->ajaxDriver->redirect = $uri;
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
	 * @return void
	 * @throws TerminateException
	 */
	public function terminate()
	{
		throw new TerminateException();
	}



	/**
	 * Conditional redirect to canonicalized URI.
	 * @return void
	 * @throws RedirectingException
	 */
	public function canonicalize()
	{
		if (!$this->isAjax() && ($this->request->isMethod('get') || $this->request->isMethod('head'))) {
			$uri = $this->createRequest($this, $this->view, $this->getGlobalState() + $this->request->params, 'redirectX');
			if ($uri !== NULL && !$this->getHttpRequest()->getUri()->isEqual($uri)) {
				throw new RedirectingException($uri, /*Nette\Web\*/IHttpResponse::S301_MOVED_PERMANENTLY);
			}
		}
	}



	/**
	 * Attempts to cache the sent entity by its last modification date
	 * @param  int    last modified time as unix timestamp
	 * @param  string strong entity tag validator
	 * @param  int    optional expiration time
	 * @return int    date of the client's cache version, if available
	 * @throws TerminateException
	 */
	public function lastModified($lastModified, $etag = NULL, $expire = NULL)
	{
		if (!Environment::isProduction()) {
			return NULL;
		}

		$httpResponse = $this->getHttpResponse();
		$match = FALSE;

		if ($lastModified > 0) {
			$httpResponse->setHeader('Last-Modified', /*Nette\Web\*/HttpResponse::date($lastModified));
		}

		if ($etag != NULL) { // intentionally ==
			$etag = '"' . addslashes($etag) . '"';
			$httpResponse->setHeader('ETag', $etag);
		}

		if ($expire !== NULL) {
			$httpResponse->expire($expire);
		}

		$ifNoneMatch = $this->getHttpRequest()->getHeader('if-none-match');
		$ifModifiedSince = $this->getHttpRequest()->getHeader('if-modified-since');
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
			$httpResponse->setCode(/*Nette\Web\*/IHttpResponse::S304_NOT_MODIFIED);
			$httpResponse->setHeader('Content-Length', '0');
			$this->terminate();

		} else {
			return $ifModifiedSince;
		}

		return NULL;
	}



	/**
	 * PresenterRequest/URL factory.
	 * @param  PresenterComponent  base
	 * @param  string   destination in format "[[module:]presenter:]view" or "signal!"
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
		if ($a == FALSE) {
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

		if (!($component instanceof Presenter) || substr($destination, -1) === '!') {
			$signal = rtrim($destination, '!');
			if ($signal == NULL) {  // intentionally ==
				throw new InvalidLinkException("Signal must be non-empty string.");
			}
			$destination = 'this';
		}

		if ($destination == NULL) {  // intentionally ==
			throw new InvalidLinkException("Destination must be non-empty string.");
		}

		// 3) presenter: view
		$current = FALSE;
		$a = strrpos($destination, ':');
		if ($a === FALSE) {
			$view = $destination === 'this' ? $this->view : $destination;
			$presenter = $this->getName();
			$presenterClass = $this->getClass();

		} else {
			$view = (string) substr($destination, $a + 1);
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
			$class = get_class($component);
			if ($signal === 'this') { // means "no signal"
				$signal = '';
				if (array_key_exists(0, $args)) {
					throw new InvalidLinkException("Extra parameter for signal '$class:$signal!'.");
				}

			} elseif (strpos($signal, self::NAME_SEPARATOR) === FALSE) { // TODO: AppForm exception
				// counterpart of signalReceived() & tryCall()
				$method = $component->formatSignalMethod($signal);
				if (!PresenterHelpers::isMethodCallable($class, $method)) {
					throw new InvalidLinkException("Unknown signal '$class:$signal!'.");
				}
				if ($args) { // convert indexed parameters to named
					PresenterHelpers::argsToParams($class, $method, $args);
				}
			}

			// counterpart of IStatePersistent
			if ($args && array_intersect_key($args, PresenterHelpers::getPersistentParams($class))) {
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
			if ($view === '') {
				/*$view = $presenterClass::$defaultView;*/ // in PHP 5.3
				/**/$view = self::$defaultView;/**/
			}

			$current = ($view === '*' || $view === $this->view) && $presenterClass === $this->getClass(); // TODO

			if ($args || $destination === 'this') {
				// counterpart of run() & tryCall()
				/*$method = $presenterClass::formatPresentMethod($view);*/ // in PHP 5.3
				/**/$method = call_user_func(array($presenterClass, 'formatPresentMethod'), $view);/**/
				if (!PresenterHelpers::isMethodCallable($presenterClass, $method)) {
					/*$method = $presenterClass::formatRenderMethod($view);*/ // in PHP 5.3
					/**/$method = call_user_func(array($presenterClass, 'formatRenderMethod'), $view);/**/
					if (!PresenterHelpers::isMethodCallable($presenterClass, $method)) {
						$method = NULL;
					}
				}

				// convert indexed parameters to named
				if ($method === NULL) {
					if (array_key_exists(0, $args)) {
						throw new InvalidLinkException("Extra parameter for '$presenter:$view'.");
					}

				} elseif ($destination === 'this') {
					PresenterHelpers::argsToParams($presenterClass, $method, $args, $this->params);

				} else {
					PresenterHelpers::argsToParams($presenterClass, $method, $args);
				}
			}

			// counterpart of IStatePersistent
			if ($args && array_intersect_key($args, PresenterHelpers::getPersistentParams($presenterClass))) {
				$this->saveState($args, $presenterClass);
			}

			$globalState = $this->getGlobalState($destination === 'this' ? NULL : $presenterClass);
			$current = $current && count($args) === count(array_intersect_assoc($args, $globalState + $this->params));
			$args += $globalState;
		}

		// ADD VIEW & SIGNAL & FLASH
		$args[self::VIEW_KEY] = $view;
		if (!empty($signal)) {
			$args[self::SIGNAL_KEY] = $component->getParamId($signal);
			$current = $current && $args[self::SIGNAL_KEY] === $this->getParam(self::SIGNAL_KEY);
		}
		if ($mode === 'redirect' && $this->hasFlashSession()) {
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
			unset($args['view']);
			$params = urldecode(http_build_query($args, NULL, ', '));
			throw new InvalidLinkException("No route for $presenter:$view($params)");
		}

		// make URL relative if possible
		if ($mode === 'link' && $this->relativeLinks) {
			$hostUri = $httpRequest->getUri()->hostUri;
			if (strncmp($uri, $hostUri, strlen($hostUri)) === 0) {
				$uri = substr($uri, strlen($hostUri));
			}
		}

		return $uri . $fragment;
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
	 * Saves state information for all subcomponents to $this->globalState.
	 * @return array
	 */
	private function getGlobalState($forClass = NULL)
	{
		$sinces = & $this->globalStateSince;

		if ($this->globalState === NULL) {
			if ($this->phase >= self::PHASE_SHUTDOWN) {
				throw new /*\*/InvalidStateException("Presenter is shutting down, cannot save state.");
			}

			if ($sinces === NULL) {
				// sinces base
				$sinces = PresenterHelpers::getPersistentComponents(get_class($this));
				foreach (PresenterHelpers::getPersistentParams(get_class($this)) as $nm => $l) {
					$sinces[$nm] = $l['since'];
				}
			}

			$state = array();
			foreach ($this->globalParams as $id => $params) {
				$prefix = $id . self::NAME_SEPARATOR;
				foreach ($params as $key => $val) {
					$state[$prefix . $key] = $val;
				}
			}

			$this->saveState($state, $forClass);

			$iterator = $this->getComponents(TRUE, 'Nette\Application\IStatePersistent');
			foreach ($iterator as $id => $component)
			{
				if ($iterator->getDepth() === 0) {
					$since = isset($sinces[$id]) ? $sinces[$id] : FALSE; // FALSE = nonpersistent
				}
				$prefix = $component->getUniqueId() . self::NAME_SEPARATOR;
				$params = array();
				$component->saveState($params);
				foreach ($params as $key => $val) {
					$state[$prefix . $key] = $val;
					$sinces[$prefix . $key] = $since; // additional sinces for speed-up
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
		$this->globalParams = array();
		$this->globalState = $this->getGlobalState();
	}



	/**
	 * Initializes $this->globalParams, $this->signal & $this->signalReceiver, $this->view, $this->scene. Called by run().
	 * @return void
	 * @throws BadRequestException if view name is not valid
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

		// init & validate $this->view & $this->scene
		$this->changeView(isset($selfParams[self::VIEW_KEY]) ? $selfParams[self::VIEW_KEY] : self::$defaultView);

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
	 * @return Nette\Web\SesssionNamespace
	 */
	public function getFlashSession()
	{
		if (empty($this->params[self::FLASH_KEY])) {
			$this->params[self::FLASH_KEY] = substr(md5(lcg_value()), 0, 4);
		}
		return $this->getSession()->getNamespace('Nette.Application.Flash/' . $this->params[self::FLASH_KEY]);
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
	 * @return Application
	 */
	public function getApplication()
	{
		return Environment::getApplication();
	}



	/**
	 * @return Nette\Web\Sesssion
	 */
	private function getSession()
	{
		return Environment::getSession();
	}

}
