<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Forms;

use Nette;



/**
 * Creates, validates and renders HTML forms.
 *
 * @author     David Grudl
 *
 * @example    forms/basic-example.php  Form definition using fluent interfaces
 * @example    forms/manual-rendering.php  Manual form rendering and separated form and rules definition
 * @example    forms/localization.php  Localization (with Zend_Translate)
 * @example    forms/custom-rendering.php  Custom form rendering
 * @example    forms/custom-validator.php  How to use custom validator
 * @example    forms/naming-containers.php  How to use naming containers
 * @example    forms/CSRF-protection.php  How to use Cross-Site Request Forgery (CSRF) form protection
 *
 * @property   string $action
 * @property   string $method
 * @property-read array $groups
 * @property-read array $httpData
 * @property   Nette\Localization\ITranslator $translator
 * @property-read array $errors
 * @property-read Nette\Utils\Html $elementPrototype
 * @property   IFormRenderer $renderer
 * @property-read boold $submitted
 */
class Form extends Container
{
	/** validator */
	const EQUAL = ':equal',
		IS_IN = ':equal',
		FILLED = ':filled',
		VALID = ':valid';

	// CSRF protection
	const PROTECTION = 'Nette\Forms\Controls\HiddenField::validateEqual';

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

	// file upload
	const MAX_FILE_SIZE = ':fileSize',
		MIME_TYPE = ':mimeType',
		IMAGE = ':image';

	/** method */
	const GET = 'get',
		POST = 'post';

	/** @internal tracker ID */
	const TRACKER_ID = '_form_';

	/** @internal protection token ID */
	const PROTECTOR_ID = '_token_';

	/** @var array of function(Form $sender); Occurs when the form is submitted and successfully validated */
	public $onSubmit;

	/** @var array of function(Form $sender); Occurs when the form is submitted and not validated */
	public $onInvalidSubmit;

	/** @var mixed or NULL meaning: not detected yet */
	private $submittedBy;

	/** @var array */
	private $httpData;

	/** @var Html  <form> element */
	private $element;

	/** @var IFormRenderer */
	private $renderer;

	/** @var Nette\Localization\ITranslator */
	private $translator;

	/** @var array of ControlGroup */
	private $groups = array();

	/** @var array */
	private $errors = array();



	/**
	 * Form constructor.
	 * @param  string
	 */
	public function __construct($name = NULL)
	{
		$this->element = Nette\Utils\Html::el('form');
		$this->element->action = ''; // RFC 1808 -> empty uri means 'this'
		$this->element->method = self::POST;
		$this->element->id = 'frm-' . $name;

		$this->monitor(__CLASS__);
		if ($name !== NULL) {
			$tracker = new Controls\HiddenField($name);
			$tracker->unmonitor(__CLASS__);
			$this[self::TRACKER_ID] = $tracker;
		}
		parent::__construct(NULL, $name);
	}



	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 * @param  IComponent
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
	final public function getForm($need = TRUE)
	{
		return $this;
	}



	/**
	 * Sets form's action.
	 * @param  mixed URI
	 * @return Form  provides a fluent interface
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
	 * @return Form  provides a fluent interface
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
	 * @param  int
	 * @return void
	 */
	public function addProtection($message = NULL, $timeout = NULL)
	{
		$session = $this->getSession()->getNamespace('Nette.Forms.Form/CSRF');
		$key = "key$timeout";
		if (isset($session->$key)) {
			$token = $session->$key;
		} else {
			$session->$key = $token = Nette\StringUtils::random();
		}
		$session->setExpiration($timeout, $key);
		$this[self::PROTECTOR_ID] = new Controls\HiddenField($token);
		$this[self::PROTECTOR_ID]->addRule(self::PROTECTION, $message, $token);
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

		if (isset($this->groups[$caption])) {
			return $this->groups[] = $group;
		} else {
			return $this->groups[$caption] = $group;
		}
	}



	/**
	 * Removes fieldset group from form.
	 * @param  string|FormGroup
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
			throw new \InvalidArgumentException("Group not found in form '$this->name'");
		}

		foreach ($group->getControls() as $control) {
			$this->removeComponent($control);
		}

		unset($this->groups[$name]);
	}



	/**
	 * Returns all defined groups.
	 * @return array of FormGroup
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
	 * @param  Nette\Localization\ITranslator
	 * @return Form  provides a fluent interface
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
	final public function getTranslator()
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
	final public function isSubmitted()
	{
		if ($this->submittedBy === NULL) {
			$this->getHttpData();
			$this->submittedBy = !empty($this->httpData);
		}
		return $this->submittedBy;
	}



	/**
	 * Sets the submittor control.
	 * @param  ISubmitterControl
	 * @return Form  provides a fluent interface
	 */
	public function setSubmittedBy(ISubmitterControl $by = NULL)
	{
		$this->submittedBy = $by === NULL ? FALSE : $by;
		return $this;
	}



	/**
	 * Returns submitted HTTP data.
	 * @return array
	 */
	final public function getHttpData()
	{
		if ($this->httpData === NULL) {
			if (!$this->isAnchored()) {
				throw new Nette\InvalidStateException('Form is not anchored and therefore can not determine whether it was submitted.');
			}
			$this->httpData = (array) $this->receiveHttpData();
		}
		return $this->httpData;
	}



	/**
	 * Fires submit/click events.
	 * @return void
	 */
	public function fireEvents()
	{
		if (!$this->isSubmitted()) {
			return;

		} elseif ($this->submittedBy instanceof ISubmitterControl) {
			if (!$this->submittedBy->getValidationScope() || $this->isValid()) {
				$this->submittedBy->click();
				$this->onSubmit($this);
			} else {
				$this->submittedBy->onInvalidClick($this->submittedBy);
				$this->onInvalidSubmit($this);
			}

		} elseif ($this->isValid()) {
			$this->onSubmit($this);

		} else {
			$this->onInvalidSubmit($this);
		}
	}



	/**
	 * Internal: receives submitted HTTP data.
	 * @return array
	 */
	protected function receiveHttpData()
	{
		$httpRequest = $this->getHttpRequest();
		if (strcasecmp($this->getMethod(), $httpRequest->getMethod())) {
			return;
		}

		if ($httpRequest->isMethod('post')) {
			$data = Nette\ArrayUtils::mergeTree($httpRequest->getPost(), $httpRequest->getFiles());
		} else {
			$data = $httpRequest->getQuery();
		}

		if ($tracker = $this->getComponent(self::TRACKER_ID, FALSE)) {
			if (!isset($data[self::TRACKER_ID]) || $data[self::TRACKER_ID] !== $tracker->getValue()) {
				return;
			}
		}

		return $data;
	}



	/********************* data exchange ****************d*g**/



	/**
	 * Returns the values submitted by the form.
	 * @return array
	 */
	public function getValues()
	{
		$values = parent::getValues();
		unset($values[self::TRACKER_ID], $values[self::PROTECTOR_ID]);
		return $values;
	}



	/********************* validation ****************d*g**/



	/**
	 * Adds error message to the list.
	 * @param  string  error message
	 * @return void
	 */
	public function addError($message)
	{
		$this->valid = FALSE;
		if ($message !== NULL && !in_array($message, $this->errors, TRUE)) {
			$this->errors[] = $message;
		}
	}



	/**
	 * Returns validation errors.
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
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
		$this->valid = NULL;
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
	 * @param  IFormRenderer
	 * @return Form  provides a fluent interface
	 */
	public function setRenderer(IFormRenderer $renderer)
	{
		$this->renderer = $renderer;
		return $this;
	}



	/**
	 * Returns form renderer.
	 * @return IFormRenderer
	 */
	final public function getRenderer()
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
				Nette\Diagnostics\Debugger::toStringException($e);
			}
		}
	}



	/********************* backend ****************d*g**/



	/**
	 * @return Nette\Http\IRequest
	 */
	protected function getHttpRequest()
	{
		return Nette\Environment::getHttpRequest();
	}



	/**
	 * @return Nette\Http\Session
	 */
	protected function getSession()
	{
		return Nette\Environment::getSession();
	}

}
