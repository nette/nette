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
 * @package    Nette\Forms
 * @version    $Id$
 */

/*namespace Nette\Forms;*/



require_once dirname(__FILE__) . '/../Forms/FormContainer.php';



/**
 * Creates, validates and renders HTML forms.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Forms
 * @example    forms/basic-example.php  Form definition using fluent interfaces
 * @example    forms/manual-rendering.php  Manual form rendering and separated form and rules definition
 * @example    forms/localization.php  Localization (with Zend_Translate)
 * @example    forms/custom-rendering.php  Custom form rendering
 * @example    forms/custom-validator.php  How to use custom validator
 * @example    forms/naming-containers.php  How to use naming containers
 * @example    forms/CSRF-protection.php  How to use Cross-Site Request Forgery (CSRF) form protection
 * @example    forms/custom-encoding.php  How to change charset
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

	// special case
	const SCRIPT = 'Nette\Forms\InstantClientScript::javascript';
	/**#@-*/

	/** tracker ID */
	const TRACKER_ID = '_form_';

	/** protection token ID */
	const PROTECTOR_ID = '_token_';

	/** @var array of event handlers; Occurs when the form is submitted and successfully validated; function(Form $sender) */
	public $onSubmit;

	/** @var array of event handlers; Occurs when the form is submitted and not validated; function(Form $sender) */
	public $onInvalidSubmit;

	/** @var mixed */
	protected $submittedBy;

	/** @var Html  <form> element */
	private $element;

	/** @var IFormRenderer */
	private $renderer;

	/** @var Nette\ITranslator */
	private $translator;

	/** @var array of FormGroup */
	private $groups = array();

	/** @var bool */
	private $isPopulated = FALSE;

	/** @var bool */
	private $valid;

	/** @var array */
	private $errors = array();

	/** @var array */
	private $encoding = 'UTF-8';



	/**
	 * Form constructor.
	 */
	public function __construct($name = NULL, $parent = NULL)
	{
		$this->element = /*Nette\Web\*/Html::el('form');
		$this->element->action = ''; // RFC 1808 -> empty uri means 'this'
		$this->element->method = 'post';
		$this->monitor(__CLASS__);
		parent::__construct($parent, $name);
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
			throw new /*\*/InvalidStateException('Nested forms are forbidden.');
		}
	}



	/**
	 * Sets form's action.
	 * @param  mixed URI
	 * @return void
	 */
	public function setAction($url)
	{
		$this->element->action = $url;
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
	 * @return void
	 */
	public function setMethod($method)
	{
		$this->element->method = strtolower($method);
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
	 * Adds distinguishing mark.
	 * @param  string
	 * @return HiddenField
	 */
	public function addTracker($name)
	{
		return $this[self::TRACKER_ID] = new HiddenField($name);
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
	 * @param  string  label
	 * @param  bool    set this group as current
	 * @return FormGroup
	 */
	public function addGroup($label = NULL, $setAsCurrent = TRUE)
	{
		$group = new FormGroup;
		$group->setOption('label', $label);
		$group->setOption('visual', TRUE);

		if ($setAsCurrent) {
			$this->setCurrentGroup($group);
		}

		if (isset($this->groups[$label])) {
			return $this->groups[] = $group;
		} else {
			return $this->groups[$label] = $group;
		}
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



	/**
	 * Set the encoding for the values.
	 * @param  string
	 * @return void
	 */
	public function setEncoding($value)
	{
		$this->encoding = empty($value) ? 'UTF-8' : strtoupper($value);
		if ($this->encoding !== 'UTF-8' && !extension_loaded('mbstring')) {
			throw new /*\*/Exception("The PHP extension 'mbstring' is required for this encoding but is not loaded.");
		}
	}



	/**
	 * Returns the encoding.
	 * @return string
	 */
	final public function getEncoding()
	{
		return $this->encoding;
	}



	/********************* translator ****************d*g**/



	/**
	 * Sets translate adapter.
	 * @param  Nette\ITranslator
	 * @return void
	 */
	public function setTranslator(/*Nette\*/ITranslator $translator = NULL)
	{
		$this->translator = $translator;
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
	 * Tells if the form was submitted.
	 * @return ISubmitterControl|FALSE  submittor control
	 */
	public function isSubmitted()
	{
		if ($this->submittedBy === NULL) {
			$this->processHttpRequest();
		}

		return $this->submittedBy;
	}



	/**
	 * Sets the submittor control.
	 * @param  ISubmitterControl
	 * @return void
	 */
	public function setSubmittedBy(ISubmitterControl $by = NULL)
	{
		$this->submittedBy = $by === NULL ? FALSE : $by;
	}



	/**
	 * Detects form submission and loads HTTP values.
	 * @param  Nette\Web\IHttpRequest  optional request object
	 * @return void
	 */
	public function processHttpRequest($httpRequest = NULL)
	{
		$this->submittedBy = FALSE;

		if ($httpRequest === NULL) {
			$httpRequest = $this->getHttpRequest();
		}
		$httpRequest->setEncoding($this->encoding);

		if (strcasecmp($this->getMethod(), 'post') === 0) {
			if (!$httpRequest->isMethod('post')) return;
			$data = /*Nette\*/Tools::arrayMergeTree($httpRequest->getPost(), $httpRequest->getFiles());

		} else {
			if (!$httpRequest->isMethod('get')) return;
			$data = $httpRequest->getQuery();
		}

		$tracker = $this->getComponent(self::TRACKER_ID, FALSE);
		if ($tracker) {
			if (!isset($data[self::TRACKER_ID]) || $data[self::TRACKER_ID] !== $tracker->getValue()) return;

		} else {
			if (!count($data)) return;
		}

		$this->submittedBy = TRUE;
		$this->loadHttpData($data);
		$this->submit();
	}



	/**
	 * Fires submit/click events.
	 * @return void
	 */
	protected function submit()
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



	/********************* data exchange ****************d*g**/



	/**
	 * Fill-in with default values.
	 * @param  array    values used to fill the form
	 * @param  bool     erase other controls
	 * @return void
	 */
	public function setDefaults($values, $erase = FALSE)
	{
		if ($values instanceof /*\*/ArrayObject) {
			$values = (array) $values;

		} elseif (!is_array($values)) {
			throw new /*\*/InvalidArgumentException("Default values must be an array, " . gettype($values) ." given.");
		}

		$cursor = & $values;
		$iterator = $this->getComponents(TRUE);
		foreach ($iterator as $name => $control) {
			$sub = $iterator->getSubIterator();
			if (!isset($sub->cursor)) {
				$sub->cursor = & $cursor;
			}
			if ($control instanceof IFormControl) {
				if ((is_array($sub->cursor) || $sub->cursor instanceof /*\*/ArrayAccess) && array_key_exists($name, $sub->cursor)) {
					$control->setValue($sub->cursor[$name]);

				} elseif ($erase) {
					$control->setValue(NULL);
				}
			}
			if ($control instanceof INamingContainer) {
				if ((is_array($sub->cursor) || $sub->cursor instanceof /*\*/ArrayAccess) && isset($sub->cursor[$name])) {
					$cursor = & $sub->cursor[$name];
				} else {
					unset($cursor);
					$cursor = NULL;
				}
			}
		}
		$this->isPopulated = TRUE;
	}



	/**
	 * Fill-in the form with HTTP data. Doesn't check if form was submitted.
	 * @param  array    user data
	 * @return void
	 */
	protected function loadHttpData(array $data)
	{
		$cursor = & $data;
		$iterator = $this->getComponents(TRUE);
		foreach ($iterator as $name => $control) {
			$sub = $iterator->getSubIterator();
			if (!isset($sub->cursor)) {
				$sub->cursor = & $cursor;
			}
			if ($control instanceof IFormControl && !$control->isDisabled()) {
				$control->loadHttpData($sub->cursor);
				if ($control instanceof ISubmitterControl && (!is_object($this->submittedBy) || $control->isSubmittedBy())) {
					$this->submittedBy = $control;
				}
			}
			if ($control instanceof INamingContainer) { // going deeper
				if (isset($sub->cursor[$name]) && is_array($sub->cursor[$name])) {
					$cursor = & $sub->cursor[$name];
				} else {
					unset($cursor);
					$cursor = NULL;
				}
			}
		}
		$this->isPopulated = TRUE;
	}



	/**
	 * Was form populated by setDefaults() or processHttpRequest() yet?
	 * @return bool
	 */
	public function isPopulated()
	{
		return $this->isPopulated;
	}



	/**
	 * Returns the values submitted by the form.
	 * @return array
	 */
	public function getValues()
	{
		if (!$this->isPopulated) {
			throw new /*\*/InvalidStateException('Form was not populated yet. Call method isSubmitted() or setDefaults().');
		}

		$values = array();
		$cursor = & $values;
		$iterator = $this->getComponents(TRUE);
		foreach ($iterator as $name => $control) {
			$sub = $iterator->getSubIterator();
			if (!isset($sub->cursor)) {
				$sub->cursor = & $cursor;
			}
			if ($control instanceof IFormControl && !$control->isDisabled() && !($control instanceof ISubmitterControl)) {
				$sub->cursor[$name] = $control->getValue();
			}
			if ($control instanceof INamingContainer) {
				$cursor = & $sub->cursor[$name];
				$cursor = array();
			}
		}
		unset($values[self::TRACKER_ID], $values[self::PROTECTOR_ID]);
		return $values;
	}



	/********************* validation ****************d*g**/



	/**
	 * Is form valid?
	 * @return bool
	 */
	public function isValid()
	{
		if ($this->valid === NULL) {
			$this->validate();
		}
		return $this->valid;
	}



	/**
	 * Performs the server side validation.
	 * @return void
	 */
	public function validate()
	{
		if (!$this->isPopulated) {
			throw new /*\*/InvalidStateException('Form was not populated yet. Call method isSubmitted() or setDefaults().');
		}

		$controls = $this->getControls();

		$this->valid = TRUE;
		foreach ($controls as $control) {
			if (!$control->getRules()->validate()) {
				$this->valid = FALSE;
			}
		}
	}



	/**
	 * Adds error message to the list.
	 * @param  string  error message
	 * @return void
	 */
	public function addError($message)
	{
		if (!in_array($message, $this->errors, TRUE)) {
			$this->errors[] = $message;
			$this->valid = FALSE;
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
	 * @return void
	 */
	public function setRenderer(IFormRenderer $renderer)
	{
		$this->renderer = $renderer;
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
		$s = call_user_func_array(array($this->getRenderer(), 'render'), $args);

		if (strcmp($this->encoding, 'UTF-8')) {
			echo mb_convert_encoding($s, 'HTML-ENTITIES', 'UTF-8');
		} else {
			echo $s;
		}
	}



	/**
	 * Renders form to string.
	 * @return bool  can throw exceptions? (hidden parameter)
	 * @return string
	 */
	public function __toString()
	{
		try {
			if (strcmp($this->encoding, 'UTF-8')) {
				return mb_convert_encoding($this->getRenderer()->render($this), 'HTML-ENTITIES', 'UTF-8');
			} else {
				return $this->getRenderer()->render($this);
			}

		} catch (/*\*/Exception $e) {
			if (func_get_args() && func_get_arg(0)) {
				throw $e;
			} else {
				trigger_error($e->getMessage(), E_USER_WARNING);
				return '';
			}
		}
	}



	/********************* backend ****************d*g**/



	/**
	 * @return Nette\Web\IHttpRequest
	 */
	protected function getHttpRequest()
	{
		return class_exists(/*Nette\*/'Environment') ? /*Nette\*/Environment::getHttpRequest() : new /*Nette\Web\*/HttpRequest;
	}



	/**
	 * @return Nette\Web\Session
	 */
	protected function getSession()
	{
		return /*Nette\*/Environment::getSession();
	}

}
