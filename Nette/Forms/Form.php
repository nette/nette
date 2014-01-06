<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette;


/**
 * Creates, validates and renders HTML forms.
 *
 * @author     David Grudl
 *
 * @property   mixed $action
 * @property   string $method
 * @property-read array $groups
 * @property   Nette\Localization\ITranslator|NULL $translator
 * @property-read bool $anchored
 * @property-read ISubmitterControl|FALSE $submitted
 * @property-read bool $success
 * @property-read array $httpData
 * @property-read array $errors
 * @property-read Nette\Utils\Html $elementPrototype
 * @property   IFormRenderer $renderer
 */
class Form extends Container
{
	/** validator */
	const EQUAL = ':equal',
		IS_IN = self::EQUAL,
		NOT_EQUAL = ':notEqual',
		FILLED = ':filled',
		BLANK = ':blank',
		REQUIRED = self::FILLED,
		VALID = ':valid';

	/** @deprecated CSRF protection */
	const PROTECTION = Controls\CsrfProtection::PROTECTION;

	// button
	const SUBMITTED = ':submitted';

	// text
	const MIN_LENGTH = ':minLength',
		MAX_LENGTH = ':maxLength',
		LENGTH = ':length',
		EMAIL = ':email',
		URL = ':url',
		REGEXP = ':regexp',
		PATTERN = ':pattern',
		INTEGER = ':integer',
		NUMERIC = ':integer',
		FLOAT = ':float',
		RANGE = ':range';

	// multiselect
	const COUNT = self::LENGTH;

	// file upload
	const MAX_FILE_SIZE = ':fileSize',
		MIME_TYPE = ':mimeType',
		IMAGE = ':image',
		MAX_POST_SIZE = ':maxPostSize';

	/** method */
	const GET = 'get',
		POST = 'post';

	/** submitted data types */
	const DATA_TEXT = 1;
	const DATA_LINE = 2;
	const DATA_FILE = 3;

	/** @internal tracker ID */
	const TRACKER_ID = '_form_';

	/** @internal protection token ID */
	const PROTECTOR_ID = '_token_';

	/** @var array of function(Form $sender); Occurs when the form is submitted and successfully validated */
	public $onSuccess;

	/** @var array of function(Form $sender); Occurs when the form is submitted and is not valid */
	public $onError;

	/** @var array of function(Form $sender); Occurs when the form is submitted */
	public $onSubmit;

	/** @var mixed or NULL meaning: not detected yet */
	private $submittedBy;

	/** @var array */
	private $httpData;

	/** @var Nette\Utils\Html  <form> element */
	private $element;

	/** @var IFormRenderer */
	private $renderer;

	/** @var Nette\Localization\ITranslator */
	private $translator;

	/** @var ControlGroup[] */
	private $groups = array();

	/** @var array */
	private $errors = array();

	/** @var Nette\Http\IRequest  used only by standalone form */
	public $httpRequest;


	/**
	 * Form constructor.
	 * @param  string
	 */
	public function __construct($name = NULL)
	{
		$this->element = Nette\Utils\Html::el('form');
		$this->element->action = ''; // RFC 1808 -> empty uri means 'this'
		$this->element->method = self::POST;
		$this->element->id = $name === NULL ? NULL : 'frm-' . $name;

		$this->monitor(__CLASS__);
		if ($name !== NULL) {
			$tracker = new Controls\HiddenField($name);
			$tracker->setOmitted();
			$this[self::TRACKER_ID] = $tracker;
		}
		parent::__construct(NULL, $name);
	}


	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 * @param  Nette\ComponentModel\IComponent
	 * @return void
	 */
	protected function attached($obj)
	{
		if ($obj instanceof self) {
			throw new Nette\InvalidStateException('Nested forms are forbidden.');
		}
	}


	/**
	 * Returns self.
	 * @return Form
	 */
	public function getForm($need = TRUE)
	{
		return $this;
	}


	/**
	 * Sets form's action.
	 * @param  mixed URI
	 * @return self
	 */
	public function setAction($url)
	{
		$this->element->action = $url;
		return $this;
	}


	/**
	 * Returns form's action.
	 * @return mixed URI
	 */
	public function getAction()
	{
		return $this->element->action;
	}


	/**
	 * Sets form's method.
	 * @param  string get | post
	 * @return self
	 */
	public function setMethod($method)
	{
		if ($this->httpData !== NULL) {
			throw new Nette\InvalidStateException(__METHOD__ . '() must be called until the form is empty.');
		}
		$this->element->method = strtolower($method);
		return $this;
	}


	/**
	 * Returns form's method.
	 * @return string get | post
	 */
	public function getMethod()
	{
		return $this->element->method;
	}


	/**
	 * Cross-Site Request Forgery (CSRF) form protection.
	 * @param  string
	 * @return Controls\CsrfProtection
	 */
	public function addProtection($message = NULL)
	{
		return $this[self::PROTECTOR_ID] = new Controls\CsrfProtection($message);
	}


	/**
	 * Adds fieldset group to the form.
	 * @param  string  caption
	 * @param  bool    set this group as current
	 * @return ControlGroup
	 */
	public function addGroup($caption = NULL, $setAsCurrent = TRUE)
	{
		$group = new ControlGroup;
		$group->setOption('label', $caption);
		$group->setOption('visual', TRUE);

		if ($setAsCurrent) {
			$this->setCurrentGroup($group);
		}

		if (!is_scalar($caption) || isset($this->groups[$caption])) {
			return $this->groups[] = $group;
		} else {
			return $this->groups[$caption] = $group;
		}
	}


	/**
	 * Removes fieldset group from form.
	 * @param  string|ControlGroup
	 * @return void
	 */
	public function removeGroup($name)
	{
		if (is_string($name) && isset($this->groups[$name])) {
			$group = $this->groups[$name];

		} elseif ($name instanceof ControlGroup && in_array($name, $this->groups, TRUE)) {
			$group = $name;
			$name = array_search($group, $this->groups, TRUE);

		} else {
			throw new Nette\InvalidArgumentException("Group not found in form '$this->name'");
		}

		foreach ($group->getControls() as $control) {
			$control->getParent()->removeComponent($control);
		}

		unset($this->groups[$name]);
	}


	/**
	 * Returns all defined groups.
	 * @return ControlGroup[]
	 */
	public function getGroups()
	{
		return $this->groups;
	}


	/**
	 * Returns the specified group.
	 * @param  string  name
	 * @return ControlGroup
	 */
	public function getGroup($name)
	{
		return isset($this->groups[$name]) ? $this->groups[$name] : NULL;
	}


	/********************* translator ****************d*g**/


	/**
	 * Sets translate adapter.
	 * @return self
	 */
	public function setTranslator(Nette\Localization\ITranslator $translator = NULL)
	{
		$this->translator = $translator;
		return $this;
	}


	/**
	 * Returns translate adapter.
	 * @return Nette\Localization\ITranslator|NULL
	 */
	public function getTranslator()
	{
		return $this->translator;
	}


	/********************* submission ****************d*g**/


	/**
	 * Tells if the form is anchored.
	 * @return bool
	 */
	public function isAnchored()
	{
		return TRUE;
	}


	/**
	 * Tells if the form was submitted.
	 * @return ISubmitterControl|FALSE  submittor control
	 */
	public function isSubmitted()
	{
		if ($this->submittedBy === NULL) {
			$this->getHttpData();
		}
		return $this->submittedBy;
	}


	/**
	 * Tells if the form was submitted and successfully validated.
	 * @return bool
	 */
	public function isSuccess()
	{
		return $this->isSubmitted() && $this->isValid();
	}


	/**
	 * Sets the submittor control.
	 * @return self
	 */
	public function setSubmittedBy(ISubmitterControl $by = NULL)
	{
		$this->submittedBy = $by === NULL ? FALSE : $by;
		return $this;
	}


	/**
	 * Returns submitted HTTP data.
	 * @return mixed
	 */
	public function getHttpData($type = NULL, $htmlName = NULL)
	{
		if ($this->httpData === NULL) {
			if (!$this->isAnchored()) {
				throw new Nette\InvalidStateException('Form is not anchored and therefore can not determine whether it was submitted.');
			}
			$data = $this->receiveHttpData();
			$this->httpData = (array) $data;
			$this->submittedBy = is_array($data);
		}
		if ($htmlName === NULL) {
			return $this->httpData;
		}
		return Helpers::extractHttpData($this->httpData, $htmlName, $type);
	}


	/**
	 * Fires submit/click events.
	 * @return void
	 */
	public function fireEvents()
	{
		if (!$this->isSubmitted()) {
			return;
		}

		$this->validate();

		if ($this->submittedBy instanceof ISubmitterControl) {
			if ($this->isValid()) {
				$this->submittedBy->onClick($this->submittedBy);
			} else {
				$this->submittedBy->onInvalidClick($this->submittedBy);
			}
		}

		if ($this->onSuccess) {
			foreach ($this->onSuccess as $handler) {
				if (!$this->isValid()) {
					$this->onError($this);
					break;
				}
				Nette\Utils\Callback::invoke($handler, $this);
			}
		} elseif (!$this->isValid()) {
			$this->onError($this);
		}
		$this->onSubmit($this);
	}


	/**
	 * Internal: returns submitted HTTP data or NULL when form was not submitted.
	 * @return array|NULL
	 */
	protected function receiveHttpData()
	{
		$httpRequest = $this->getHttpRequest();
		if (strcasecmp($this->getMethod(), $httpRequest->getMethod())) {
			return;
		}

		if ($httpRequest->isMethod('post')) {
			$data = Nette\Utils\Arrays::mergeTree($httpRequest->getPost(), $httpRequest->getFiles());
		} else {
			$data = $httpRequest->getQuery();
			if (!$data) {
				return;
			}
		}

		if ($tracker = $this->getComponent(self::TRACKER_ID, FALSE)) {
			if (!isset($data[self::TRACKER_ID]) || $data[self::TRACKER_ID] !== $tracker->getValue()) {
				return;
			}
		}

		return $data;
	}


	/********************* validation ****************d*g**/


	public function validate(array $controls = NULL)
	{
		$this->cleanErrors();
		if ($controls === NULL && $this->submittedBy instanceof ISubmitterControl) {
			$controls = $this->submittedBy->getValidationScope();
		}
		$this->validateMaxPostSize();
		parent::validate($controls);
	}


	public function validateMaxPostSize()
	{
		if (!$this->submittedBy || strcasecmp($this->getMethod(), 'POST') || empty($_SERVER['CONTENT_LENGTH'])) {
			return;
		}
		$maxSize = ini_get('post_max_size');
		$units = array('k' => 10, 'm' => 20, 'g' => 30);
		if (isset($units[$ch = strtolower(substr($maxSize, -1))])) {
			$maxSize <<= $units[$ch];
		}
		if ($maxSize > 0 && $maxSize < $_SERVER['CONTENT_LENGTH']) {
			$this->addError(sprintf(Rules::$defaultMessages[self::MAX_FILE_SIZE], $maxSize));
		}
	}


	/**
	 * Adds global error message.
	 * @param  string  error message
	 * @return void
	 */
	public function addError($message)
	{
		$this->errors[] = $message;
	}


	/**
	 * Returns global validation errors.
	 * @return array
	 */
	public function getErrors()
	{
		return array_unique(array_merge($this->errors, parent::getErrors()));
	}


	/**
	 * @return bool
	 */
	public function hasErrors()
	{
		return (bool) $this->getErrors();
	}


	/**
	 * @return void
	 */
	public function cleanErrors()
	{
		$this->errors = array();
	}


	/**
	 * Returns form's validation errors.
	 * @return array
	 */
	public function getOwnErrors()
	{
		return array_unique($this->errors);
	}


	/** @deprecated */
	public function getAllErrors()
	{
		trigger_error(__METHOD__ . '() is deprecated; use getErrors() instead.', E_USER_DEPRECATED);
		return $this->getErrors();
	}


	/********************* rendering ****************d*g**/


	/**
	 * Returns form's HTML element template.
	 * @return Nette\Utils\Html
	 */
	public function getElementPrototype()
	{
		return $this->element;
	}


	/**
	 * Sets form renderer.
	 * @return self
	 */
	public function setRenderer(IFormRenderer $renderer = NULL)
	{
		$this->renderer = $renderer;
		return $this;
	}


	/**
	 * Returns form renderer.
	 * @return IFormRenderer
	 */
	public function getRenderer()
	{
		if ($this->renderer === NULL) {
			$this->renderer = new Rendering\DefaultFormRenderer;
		}
		return $this->renderer;
	}


	/**
	 * Renders form.
	 * @return void
	 */
	public function render()
	{
		$args = func_get_args();
		array_unshift($args, $this);
		echo call_user_func_array(array($this->getRenderer(), 'render'), $args);
	}


	/**
	 * Renders form to string.
	 * @return bool  can throw exceptions? (hidden parameter)
	 * @return string
	 */
	public function __toString()
	{
		try {
			return $this->getRenderer()->render($this);

		} catch (\Exception $e) {
			if (func_get_args() && func_get_arg(0)) {
				throw $e;
			} else {
				trigger_error("Exception in " . __METHOD__ . "(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", E_USER_ERROR);
			}
		}
	}


	/********************* backend ****************d*g**/


	/**
	 * @return Nette\Http\IRequest
	 */
	private function getHttpRequest()
	{
		if (!$this->httpRequest) {
			$factory = new Nette\Http\RequestFactory;
			$this->httpRequest = $factory->createHttpRequest();
		}
		return $this->httpRequest;
	}


	/**
	 * @return array
	 */
	public function getToggles()
	{
		$toggles = array();
		foreach ($this->getControls() as $control) {
			foreach ($control->getRules()->getToggles(TRUE) as $id => $hide) {
				$toggles[$id] = empty($toggles[$id]) ? $hide : TRUE;
			}
		}
		return $toggles;
	}

}
