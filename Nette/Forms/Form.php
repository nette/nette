<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
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
 * @property   Nette\ITranslator $translator
 * @property-read array $errors
 * @property-read Nette\Web\Html $elementPrototype
 * @property   IFormRenderer $renderer
 * @property-read boold $submitted
 */
class Form extends FormContainer
{
	/**#@+ operation name */
	const EQUAL = ':equal';
	const IS_IN = ':equal';
	const FILLED = ':filled';
	const VALID = ':valid';

	// button
	const SUBMITTED = ':submitted';

	// text
	const MIN_LENGTH = ':minLength';
	const MAX_LENGTH = ':maxLength';
	const LENGTH = ':length';
	const EMAIL = ':email';
	const URL = ':url';
	const REGEXP = ':regexp';
	const INTEGER = ':integer';
	const NUMERIC = ':integer';
	const FLOAT = ':float';
	const RANGE = ':range';

	// file upload
	const MAX_FILE_SIZE = ':fileSize';
	const MIME_TYPE = ':mimeType';
	const IMAGE = ':image';
	/**#@-*/

	/**#@+ method */
	const GET = 'get';
	const POST = 'post';
	/**#@-*/

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

	/** @var Nette\ITranslator */
	private $translator;

	/** @var array of FormGroup */
	private $groups = array();

	/** @var array */
	private $errors = array();



	/**
	 * Form constructor.
	 * @param  string
	 */
	public function __construct($name = NULL)
	{
		$this->element = Nette\Web\Html::el('form');
		$this->element->action = ''; // RFC 1808 -> empty uri means 'this'
		$this->element->method = self::POST;
		$this->element->id = 'frm-' . $name;

		$this->monitor(__CLASS__);
		if ($name !== NULL) {
			$tracker = new HiddenField($name);
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
			throw new \InvalidStateException('Nested forms are forbidden.');
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
			throw new \InvalidStateException(__METHOD__ . '() must be called until the form is empty.');
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
			$session->$key = $token = md5(uniqid('', TRUE));
		}
		$session->setExpiration($timeout, $key);
		$this[self::PROTECTOR_ID] = new HiddenField($token);
		$this[self::PROTECTOR_ID]->addRule(':equal', empty($message) ? 'Security token did not match. Possible CSRF attack.' : $message, $token);
	}



	/**
	 * Adds fieldset group to the form.
	 * @param  string  caption
	 * @param  bool    set this group as current
	 * @return FormGroup
	 */
	public function addGroup($caption = NULL, $setAsCurrent = TRUE)
	{
		$group = new FormGroup;
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

		} elseif ($name instanceof FormGroup && in_array($name, $this->groups, TRUE)) {
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
	 * @return FormGroup
	 */
	public function getGroup($name)
	{
		return isset($this->groups[$name]) ? $this->groups[$name] : NULL;
	}



	/********************* translator ****************d*g**/



	/**
	 * Sets translate adapter.
	 * @param  Nette\ITranslator
	 * @return Form  provides a fluent interface
	 */
	public function setTranslator(Nette\ITranslator $translator = NULL)
	{
		$this->translator = $translator;
		return $this;
	}



	/**
	 * Returns translate adapter.
	 * @return Nette\ITranslator|NULL
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
				throw new \InvalidStateException('Form is not anchored and therefore can not determine whether it was submitted.');
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

		$httpRequest->setEncoding('utf-8');
		if ($httpRequest->isMethod('post')) {
			$data = Nette\ArrayTools::mergeTree($httpRequest->getPost(), $httpRequest->getFiles());
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
	 * @return Nette\Web\Html
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
	 * @return IFormRenderer|NULL
	 */
	final public function getRenderer()
	{
		if ($this->renderer === NULL) {
			$this->renderer = new ConventionalRenderer;
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
				Nette\Debug::toStringException($e);
			}
		}
	}



	/********************* backend ****************d*g**/



	/**
	 * @return Nette\Web\IHttpRequest
	 */
	protected function getHttpRequest()
	{
		return class_exists('Nette\Environment') ? Nette\Environment::getHttpRequest() : new Nette\Web\HttpRequest;
	}



	/**
	 * @return Nette\Web\Session
	 */
	protected function getSession()
	{
		return Nette\Environment::getSession();
	}

}
