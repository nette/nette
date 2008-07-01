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



require_once dirname(__FILE__) . '/../Application/Control.php';

require_once dirname(__FILE__) . '/../Application/IPresenter.php';



/**
 * Presenter object represents a webpage instance. It executes all the logic for the request.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Application
 * @version    $Revision$ $Date$
 */
abstract class Presenter extends Control implements IPresenter
{
	/** @see $this->getPhase() */
	const STARTUP = '1 STARTUP';
	const PREPARING = '2 PREPARING';
	const SIGNAL_HANDLING = '3 SIGNAL HANDLING';
	const RENDERING = '4 RENDERING';
	const SHUTDOWN = '5 SHUTDOWN';

	const LINK_SILENT = 1;
	const LINK_WARNING = 2;
	const LINK_EXCEPTION = 3;

	const SIGNAL_KEY = 'do';
	const VIEW_KEY = 'view';

	const THIS_VIEW = '!';

	/** @var string */
	public static $defaultView = 'default';

	/** @var bool TODO: asi dat do Application */
	public static $invalidLinkMode = self::LINK_WARNING;

	/** @var PresenterRequest */
	private $request;

	/** @var int */
	private $phase;

	/**
	 * Lists of all components identified by a uniqueId starting from this page.
	 * @var array of Nette::IComponent
	 */
	private $globalComponents = array();

	/** @var array */
	private $globalParams;

	/** @var string */
	private $view;

	/** @var ITemplate */
	private $template;

	/** @var AjaxResponse */
	private $ajaxResponse;

	/** @var string */
	private $signalReceiver;

	/** @var string */
	private $signal;

	/** @var bool */
	private $renderFinished = FALSE;

	/** @var bool */
	private $partialMode;

	/** @var array  cache for createRequest(), not static! */
	private $requestCache = array();

	/** @var bool  automatically call canonicalize() */
	public $autoCanonicalize = FALSE;

	/** @var IRouter  cached value for createRequest() & createSubRequest() */
	private $router;

	/** @var Nette::Web::IHttpRequest  cached value for better performance */
	private $httpRequest;

	/** @var Nette::Web::IHttpResponse  cached value for better performance */
	private $httpResponse;




	/**
	 * @return PresenterRequest
	 */
	final public function getRequest()
	{
		return clone $this->request;
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
	 * @return void
	 */
	public function run(PresenterRequest $request)
	{
		try {
			try {
				$this->request = $request;
				$this->router = Environment::getApplication()->getRouter();
				$this->httpRequest = Environment::getHttpRequest();
				$this->httpResponse = Environment::getHttpResponse();

				// PHASE 1: STARTUP
				$this->phase = self::STARTUP;
				$this->initGlobalParams();
				$this->registerComponent($this->getUniqueId(), $this);
				$this->startup();
				//if ($this->autoCanonicalize) {
				//	$this->canonicalize();
				//}

				// PHASE 2: PREPARING
				$this->phase = self::PREPARING;
				$this->beforePrepare();
				// $this->prepare{viewname}();
				$this->tryCall($this->formatPrepareMethod($this->getView()), $this->params);


				// PHASE 3: SIGNAL HANDLING
				$this->phase = self::SIGNAL_HANDLING;
				$this->processSignal();
				// save component tree persistent state
				$this->globalParams = $this->getGlobalParams();


				// PHASE 4: RENDERING
				if ($this->httpRequest->getMethod() === 'HEAD') {
					$this->abort();
				}

				$this->phase = self::RENDERING;

				if ($this->isPartialMode()) {
					$this->startPartialMode();
				}

				$this->beforeRender();
				// $this->render{viewname}();
				$this->tryCall($this->formatRenderMethod($this->getView()), $this->params);

				if (!$this->isRenderFinished()) {
					$this->renderTemplate();
				}

				if ($this->isPartialMode()) {
					$this->finishPartialMode();
				}

				$e = NULL;

			} catch (AbortException $e) {
				// continue with shutting down
			} /* finally */ {
				// PHASE 5: SHUTDOWN
				$this->phase = self::SHUTDOWN;
				$this->unregisterComponent($this);
				$this->shutdown($e);
			}

		} catch (Exception $e) {
			$this->renderError($e);
			//$this->abort($e);
		}

		if ($e) throw $e;
	}



	/**
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
	 * @return void
	 */
	protected function beforePrepare()
	{
	}



	/**
	 * @return void
	 */
	protected function beforeRender()
	{
	}



	/**
	 * @param  Exception
	 * @return void
	 */
	protected function shutdown(Exception $cause = NULL)
	{
	}



	/********************* signal handling ****************d*g**/



	/**
	 * @return void
	 */
	final protected function processSignal()
	{
		if ($this->signal === NULL) return;

		if (!isset($this->globalComponents[$this->signalReceiver])) {
			throw new SignalException('The component to receive signal is missing.');
		}

		$component = $this->globalComponents[$this->signalReceiver];
		if (!$component instanceof ISignalReceiver) {
			throw new SignalException('Component is not ISignalReceiver.');
		}

		if ($component === $this) {
			$realView = $this->getViewForSignal($this->signal);
			if ($realView !== FALSE && $realView !== $this->getView()) {
				throw new SignalException("Invalid signal '$this->signal' for view '{$this->getView()}.");
			}
		}

		// auto invalidate
		if ($this->isPartialMode() && $component instanceof Control) {
			$component->invalidate();
		}

		$component->signalReceived($this->signal);
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
	 * Returns current view name (as lower-case non-empty string).
	 * @return string
	 */
	final public function getView()
	{
		return $this->view;
	}



	/**
	 * Switch current view.
	 * @param  string
	 * @return void
	 */
	public function changeView($view)
	{
		$this->view = $view == NULL ? self::$defaultView : $view;  // intentionally ==
	}



	/**
	 * @return void
	 */
	protected function renderTemplate()
	{
		$template = $this->getTemplate();

		if ($template instanceof Template && (!$template->getFile() || !isset($template->content))) {
			$found = FALSE;
			$files = $this->formatTemplateFiles($this->request->getPresenterName(), $this->getView());
			foreach ($files as $file) {
				if (is_file($file)) {
					$found = TRUE;
					break;
				}
			}

			if (!$found) {
				throw new /*::*/FileNotFoundException("Page not found. Missing template '$files[0]'.");
			}

			if ($template->getFile()) { // has layout?
				$template->addTemplate('content', $file);
			} else {
				$template->setFile($file);
			}
		}

		$template->render();

		if ($this->httpResponse instanceof /*Nette::Web::*/HttpResponse) {
			$this->httpResponse->fixIE();
		}

		$this->renderFinished();
	}



	/**
	 * @return void
	 */
	final public function renderFinished()
	{
		if ($this->renderFinished) {
			throw new /*::*/InvalidStateException("Already rendered.");
		}
		$this->renderFinished = TRUE;
	}



	/**
	 * @return bool
	 */
	final public function isRenderFinished()
	{
		return $this->renderFinished;
	}



	/**
	 * @return ITemplate
	 */
	final public function getTemplate()
	{
		if ($this->template === NULL) {
			$value = $this->createTemplate();
			if (!($value instanceof ITemplate)) {
				throw new /*::*/UnexpectedValueException('Object ITemplate was expected.');
			}
			$this->template = $value;
		}
		return $this->template;
	}



	/**
	 * @return ITemplate
	 */
	protected function createTemplate()
	{
		$template = new Template;

		$template->component = $this;
		$template->presenter = $this;
		$template->baseUri = /*Nette::*/Environment::getVariable('basePath');

		$files = $this->formatTemplateLayoutFiles($this->request->getPresenterName());
		foreach ($files as $file) {
			if (is_file($file)) {
				$template->setFile($file);
				break;
			}
		}

		return $template;
	}



	/**
	 * Formats layout template file names.
	 * @param  string
	 * @return array
	 */
	protected static function formatTemplateLayoutFiles($presenter)
	{
		$root = Environment::getVariable('templatesDir');
		$presenter = str_replace(':', 'Module/', $presenter);
		$module = substr($presenter, 0, (int) strrpos($presenter, '/'));
		return array(
			"$root/$presenter/@layout.phtml",
			"$root/$presenter.@layout.phtml",
			"$root/$module/@layout.phtml",
			"$root/@layout.phtml",
			"$root/layout.phtml", // back compatibility
		);
	}



	/**
	 * Formats view template file names.
	 * @param  string
	 * @param  string
	 * @return array
	 */
	protected static function formatTemplateFiles($presenter, $view)
	{
		if (!preg_match("#^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$#", $view)) {
			return array();
		}
		$root = Environment::getVariable('templatesDir');
		$presenter = str_replace(':', 'Module/', $presenter);
		return array(
			"$root/$presenter/$view.phtml",
			"$root/$presenter.$view.phtml",
		);
	}



	/**
	 * Formats prepare & execute method name.
	 * @param  string
	 * @return string
	 */
	protected static function formatPrepareMethod($name)
	{
		return $name == NULL ? NULL : 'prepare' . $name; // intentionally ==
	}



	/**
	 * Formats view rendering method name.
	 * @param  string
	 * @return string
	 */
	protected static function formatRenderMethod($name)
	{
		return $name == NULL ? NULL : 'render' . $name; // intentionally ==
	}



	/********************* partial rendering ****************d*g**/



	/**
	 * Is in partial mode? (AJAX request).
	 * @return bool
	 */
	public function isPartialMode()
	{
		if ($this->partialMode === NULL) {
			$this->partialMode = $this->httpRequest->isAjax();
		}
		return $this->partialMode;
	}



	/**
	 * Save the partial content to the table.
	 * @param  string
	 * @param  string
	 * @return void
	 */
	public function addPartial($id, $content)
	{
		$this->ajaxResponse->addPartial($id, $content);
	}



	protected function startPartialMode()
	{
		$this->ajaxResponse = $this->createAjaxResponse();
		$this->ajaxResponse->open();
		ob_start(); // discard any output
	}



	protected function finishPartialMode()
	{
		ob_end_clean(); // discard any output
		/*
		if ($this->isInvalid()) {
			$this->ajaxResponse->redirect($this->link(self::THIS_VIEW));

		} else*/ {
			$state = array();
			$this->saveState($state);
			$this->ajaxResponse->setState($state);
		}
		$this->ajaxResponse->close();
	}



	/**
	 * @return AjaxResponse
	 */
	protected function createAjaxResponse()
	{
		return new AjaxResponse;
	}



	/********************* navigation & flow ****************d*g**/



	/**
	 * Generate URL to presenter/view/signal.
	 * @param  string
	 * @param  array|mixed
	 * @return string
	 */
	public function link($destination, $args = array())
	{
		if (!is_array($args)) {
			$args = func_get_args();
			array_shift($args);
		}

		try {
			if (substr($destination, -1) === '!') {
				$signal = rtrim($destination, '!');
				if ($signal != NULL) { // intentionally ==
					$this->argsForSignal($signal, $args);
				}
				return $this->createSubRequest($this->getUniqueId(), $signal, $args);

			} else {
				return $this->createRequest($destination, $args);
			}

		} catch (Exception $e) {
			if (self::$invalidLinkMode === self::LINK_WARNING) {
				trigger_error($e->getMessage(), E_USER_WARNING);
			} elseif (self::$invalidLinkMode === self::LINK_EXCEPTION) {
				throw new LinkException($e);
			}
			return '#';
		}
	}



	/**
	 * Forward to another presenter/view.
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
	 * @throws AbortException
	 */
	public function redirectUri($uri, $code = /*Nette::Web::*/IHttpResponse::S303_POST_GET)
	{
		if ($this->isPartialMode()) {
			$this->ajaxResponse->redirect($uri);

		} else {
			if (substr($uri, 0, 2) === '//') {
				$uri = $this->httpRequest->getUri()->scheme . ':' . $uri;
			} elseif (substr($uri, 0, 1) === '/') {
				$uri = $this->httpRequest->getUri()->hostUri . $uri;
			}

			$this->httpResponse->setCode($code);
			$this->httpResponse->setHeader('Location: ' . $uri);
			echo '<h1>Redirect</h1><p><a href="', htmlSpecialChars($uri), '">Please click here to continue</a>.</p>';
		}

		$this->abort();
	}



	/**
	 * Link to myself.
	 * @param  bool   TODO
	 * @return string
	 */
	public function backlink($full = TRUE)
	{
		// TODO: implement $full
		return $this->request->getPresenterName() . ':' . $this->getView();
	}



	/**
	 * Ends presenter execution.
	 * @return void
	 * @throws AbortException
	 */
	public function abort()
	{
		throw new AbortException();
	}



	/**
	 * Conditional redirect to canonicalized URI.
	 * @return void
	 * @throws AbortException
	 */
	final public function canonicalize()
	{
		if ($this->httpRequest->getMethod() === 'POST' || $this->httpRequest->isAjax()) {
			return;
		}

		// TODO: what about signal args
		$uri = $this->createSubRequest($this->getSignalReceiver(TRUE), $this->getSignal(), array());

		if (!$this->httpRequest->getUri()->isEqual($uri)) {
			$this->redirectUri($uri, /*Nette::Web::*/IHttpResponse::S301_MOVED_PERMANENTLY);
		}
	}



	/**
	 * @return void
	 * @throws AbortException
	 */
	public function lastModified($lastModified, $expire = NULL)
	{
		if (Environment::getName() === Environment::DEVELOPMENT) {
			return;
		}

		if ($expire !== NULL) {
			$this->httpResponse->expire($expire);
		}

		$ifModifiedSince = $this->httpRequest->getHeader('if-modified-since');
		if ($ifModifiedSince !== NULL) {
			$ifModifiedSince = strtotime($ifModifiedSince);
			if ($lastModified <= $ifModifiedSince) {
				$this->httpResponse->setCode(/*Nette::Web::*/IHttpResponse::S304_NOT_MODIFIED);
				$this->abort();
			}
		}

		$this->httpResponse->setHeader('Last-Modified: ' . /*Nette::Web::*/HttpResponse::date($lastModified));
		// TODO: support for ETag
	}



	/**
	 * PresenterRequest/URL factory.
	 * @param  string   destination in format "[[module:]presenter:][view]"
	 * @param  array    array of arguments
	 * @param  bool     return PresenterRequest or URL?
	 * @return string|PresenterRequest
	 */
	protected function createRequest($destination, array $args, $returnUri = TRUE)
	{
		// TODO: add cache here!

		// parse $destination
		$destination = explode(':', $destination);

		$view = array_pop($destination);

		if (!count($destination)) {
			$presenter = $this->request->getPresenterName();
			$presenterClass = $this->getClass();

		} elseif ($destination[0] === '') {
			unset($destination[0]);
			$presenter = implode(':', $destination);
			$presenterClass = Environment::getApplication()->getPresenterLoader()->getPresenterClass($presenter);

		} else {
			$presenter = explode(':', $this->request->getPresenterName());
			array_splice($presenter, -1, 1, $destination);
			$presenter = implode(':', $presenter);
			$presenterClass = Environment::getApplication()->getPresenterLoader()->getPresenterClass($presenter);
		}

		if (is_subclass_of($presenterClass, __CLASS__)) {
			if ($view === '') {
				/*$view = $presenterClass::$defaultView;*/ // in PHP 5.3
				/**/$view = eval("return $presenterClass::\$defaultView;");/**/
			}

			if ($args) {
				/*$method = $presenterClass::formatRenderMethod($view);*/ // in PHP 5.3
				/**/$method = call_user_func(array($presenterClass, 'formatRenderMethod'), $view);/**/
				if (PresenterHelpers::isMethodCallable($presenterClass, $method)) {
					PresenterHelpers::argsToParams($presenterClass, $method, $args);

				} elseif (array_key_exists(0, $args)) { // is needed argument -> params convertion?
					throw new /*::*/InvalidArgumentException("Extra parameters for '$presenter:$view'.");
				}
			}

			/*if (strcasecmp($presenter, $this->request->getPresenterName()) === 0) {
				$args += $this->getGlobalParams();
			} else {*/
				$this->saveState($args, $presenterClass);
			/*}*/
		}

		$args[self::VIEW_KEY] = $view;

		$request = new PresenterRequest(
			$presenter,
			PresenterRequest::FORWARD,
			$args
		);

		if ($returnUri) {
			$uri = $this->router->constructUrl($request, $this->httpRequest);
			if ($uri === NULL) {
				throw new ApplicationException('No route.');
			}
			return $uri;

		} else {
			return $request;
		}
	}



	/**
	 * PresenterRequest/URL factory.
	 * @param  string   optional signal executor
	 * @param  string   optional signal to execute
	 * @param  array    optional signal arguments
	 * @param  bool     return PresenterRequest or URL?
	 * @return string|PresenterRequest
	 */
	protected function createSubRequest($componentId, $signal, $cparams, $returnUri = TRUE)
	{
		// TODO: add cache here!
		$presenterClass = $this->getClass();
		$view = $this->getView();
		$params = array();

		$method = $this->formatRenderMethod($view);
		if (PresenterHelpers::isMethodCallable($presenterClass, $method)) {
			foreach (PresenterHelpers::getMethodParams($presenterClass, $method) as $name => $def) {
				if (isset($this->params[$name])) {
					$params[$name] = $this->params[$name];
				}
			}
		}

		if ($componentId === '') { // self
			if ($signal != NULL) { // intentionally ==
				$realView = $this->getViewForSignal($signal);
				if ($realView === FALSE) {
					$params[self::SIGNAL_KEY] = strtolower($signal);
				} else {
					$view = $signal;
					$params[self::SIGNAL_KEY] = NULL;
				}
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

		$this->saveState($params);
		$params += $this->getGlobalParams();
		$params[self::VIEW_KEY] = $view;

		$request = new PresenterRequest(
			$this->request->getPresenterName(),
			PresenterRequest::FORWARD,
			$params
		);

		if ($returnUri) {
			$uri = $this->router->constructUrl($request, $this->httpRequest);
			if ($uri === NULL) {
				throw new ApplicationException('No route.');
			}
			return $uri;

		} else {
			return $request;
		}
	}



	/********************* interface IStatePersistent ****************d*g**/



	/**
	 * Saves state information for all subcomponents.
	 * @return array
	 */
	public function getGlobalParams()
	{
		if ($this->phase > 3) {
			return $this->globalParams;
		}

		$state = array();
		foreach ($this->globalComponents as $id => $component)
		{
			if ($component instanceof IStatePersistent) {
				$params = array();
				$component->saveState($params);
				if ($id === '') {
					$state = $params + $state;
				} else {
					$prefix = $id . self::NAME_SEPARATOR;
					foreach ($params as $key => $val) {
						$state[$prefix . $key] = $val;
					}
				}
			}
		}
		return $state;
	}



	/**
	 * Initializes $this->globalParams, $this->signal & $this->signalReceiver, $this->view.
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

		// init $this->view
		if (isset($selfParams[self::VIEW_KEY])) {
			$view = $selfParams[self::VIEW_KEY];
			$realView = $this->getViewForSignal($view);
			if ($realView !== FALSE) {
				$selfParams[self::SIGNAL_KEY] = $view;
				$view = $realView;
			}
			$this->changeView($view);
		} else {
			$this->changeView(NULL);
		}

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



	/**
	 * @param  string
	 * @return string|FALSE
	 */
	private function getViewForSignal($signal)
	{
		$annotations = PresenterHelpers::getMethodAnnotations(
			$this->getClass(),
			$this->formatSignalMethod($signal)
		);
		return isset($annotations['view']) ? $annotations['view'] : FALSE;
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



	/********************* default views ****************d*g**/



	/**
	 * @param  Exception
	 * @return void
	 */
	protected function renderError(Exception $exception)
	{
		if ($this->isPartialMode()) {
			$this->ajaxResponse->error((string) $exception);

		} else {
			if (/*Nette::*/Debug::isEnabled()) throw $exception;

			$code = /*Nette::Web::*/IHttpResponse::S500_INTERNAL_SERVER_ERROR;
			$this->httpResponse->setCode($code);

			$title = 'Error';
			$message = '';
			require dirname(__FILE__) . '/templates/error.phtml';
		}

		$this->renderFinished = TRUE;
	}

}
