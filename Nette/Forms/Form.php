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
 * @package    Nette::Forms
 * @version    $Id$
 */

/*namespace Nette::Forms;*/



require_once dirname(__FILE__) . '/../Forms/FormContainer.php';



/**
 * Creates, validates and renders HTML forms.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Forms
 */
class Form extends FormContainer
{
	// common
	const EQUAL = ':equal';
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
	const NUMERIC = ':numeric';
	const FLOAT = ':float';
	const RANGE = ':range';

	// file upload
	const MAX_FILE_SIZE = ':fileSize';
	const MIME_TYPE = ':mimeType';

	// special case
	const SCRIPT = /*Nette::Forms::*/'InstantClientScript::javascript';


	/** Tracker ID */
	const TRACKER_ID = '_form_';

	/** @var array - function($sender, $submittor) */
	public $onSubmit;

	/** @var bool */
	protected $isPost = TRUE;

	/** @var mixed */
	protected $submittedBy;

	/** @var Html  <form> element */
	private $element;

	/** @var IFormRenderer */
	private $renderer;

	/** @var Nette::ITranslator */
	private $translator;

	/** @var Nette::Web::IHttpRequest */
	private $httpRequest;

	/** @var array of FormGroup */
	private $groups = array();

	/** @var bool */
	private $isPopulated = FALSE;

	/** @var bool */
	private $valid;

	/** @var array */
	private $errors = array();



	/**
	 * Form constructor.
	 */
	public function __construct($name = NULL, $parent = NULL)
	{
		$this->element = /*Nette::Web::*/Html::el('form');
		$this->setAction(''); // RFC 1808 -> empty uri means 'this'
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
			throw new /*::*/InvalidStateException('Nested forms are forbidden.');
		}
	}



	/**
	 * Sets form's action and method.
	 * @param  mixed URI
	 * @param  bool  use POST method to submit the form?
	 * @return void
	 */
	public function setAction($url, $isPost = NULL)
	{
		if ($isPost !== NULL) {
			$this->isPost = (bool) $isPost;
		}
		$this->element->action = $url;
		$this->element->method = $this->isPost ? 'post' : 'get';
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
	 * @return HiddenField
	 */
	public function addTracker($name)
	{
		// TODO: implement Cross-Site Request Forgery token
		$this[self::TRACKER_ID] = new HiddenField;
		$this[self::TRACKER_ID]->setValue($name);
	}



	/**
	 * Iterates over all form controls.
	 * @return ::ArrayIterator
	 */
	public function getControls()
	{
		return $this->getComponents(TRUE, 'Nette::Forms::IFormControl');
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
		if ($setAsCurrent) {
			$this->setCurrentGroup($group);
		}
		return $this->groups[] = $group;
	}



	/**
	 * Returns all defined groups.
	 * @return array of FormGroup
	 */
	public function getGroups()
	{
		return $this->groups;
	}



	/********************* translator ****************d*g**/



	/**
	 * Sets translate adapter.
	 * @param  Nette::ITranslator
	 * @return void
	 */
	public function setTranslator(/*Nette::*/ITranslator $translator = NULL)
	{
		$this->translator = $translator;
	}



	/**
	 * Returns translate adapter.
	 * @return Nette::ITranslator|NULL
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
			$this->detectSubmission();
		}

		return $this->submittedBy;
	}



	/**
	 * Sets the submittor control.
	 * @params ISubmitterControl
	 * @return void
	 */
	public function setSubmittedBy(ISubmitterControl $by = NULL)
	{
		$this->submittedBy = $by === NULL ? FALSE : $by;
	}



	/**
	 * Detects form submission and loads HTTP values.
	 * @return void
	 */
	protected function detectSubmission()
	{
		$this->submittedBy = FALSE;

		$request = $this->httpRequest ? $this->httpRequest : new /*Nette::Web::*/HttpRequest;

		// standalone mode
		if ($this->isPost xor $request->getMethod() === 'POST') return;

		$tracker = $this->getComponent(self::TRACKER_ID);
		if ($tracker) {
			$val = $this->isPost ? $request->getPost(self::TRACKER_ID) : $request->getQuery(self::TRACKER_ID);
			if ($val !== $tracker->getValue()) return;
		}

		$this->submittedBy = TRUE;

		if ($this->isPost) {
			$this->loadHttpData(self::arrayAppend($request->getPost(), $request->getFiles()));

		} else {
			$this->loadHttpData($request->getQuery());
		}

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
				$this->submittedBy->Click();
				$this->onSubmit($this);
			}

		} elseif ($this->isValid()) {
			$this->onSubmit($this);
		}
	}



	/**
	 * Sets HTTP request object.
	 * @param  Nette::Web::IHttpRequest
	 * @return void
	 */
	public function setHttpRequest(/*Nette::Web::*/IHttpRequest $httpRequest)
	{
		$this->httpRequest = $httpRequest;
		if ($this->submittedBy !== NULL) {
			$this->detectSubmission();
		}
	}



	/**
	 * Returns HTTP request object.
	 * @return void
	 */
	public function getHttpRequest()
	{
		return $this->httpRequest;
	}



	/********************* data exchange ****************d*g**/



	/**
	 * Fill-in with default values.
	 * @param  array    values used to fill the form
	 * @return void
	 */
	public function setDefaults(array $values)
	{
		// tracker value cannot be changed
		$tracker = $this->getComponent(self::TRACKER_ID);
		if ($tracker) {
			$values[self::TRACKER_ID] = $tracker->getValue();
		}

		$cursor = & $values;
		$iterator = $this->getComponents(TRUE);
		foreach ($iterator as $name => $control) {
			$sub = $iterator->getSubIterator();
			if (!isset($sub->cursor)) {
				$sub->cursor = & $cursor;
			}
			if ($control instanceof IFormControl) {
				if (isset($sub->cursor[$name])) {
					$control->setValue($sub->cursor[$name]);
				} else {
					$control->setValue(NULL);
				}
			}
			if ($control instanceof INamingContainer) {
				if (is_array($sub->cursor) && isset($sub->cursor[$name])) {
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
	public function loadHttpData(array $data)
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
				if ($control instanceof ISubmitterControl && ($this->submittedBy === TRUE || $control->isSubmittedBy())) {
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
	 * Was form populated by setDefaults() or populate() yet?
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
			throw new /*::*/InvalidStateException('Form was not populated yet. Call method isSubmitted() or setDefaults().');
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
		unset($values[self::TRACKER_ID]);
		return $values;
	}



	/**
	 * Recursively appends elements of remaining keys from the second array to the first.
	 * @param  array
	 * @param  array
	 * @return array
	 */
	public static function arrayAppend($arr1, $arr2)
	{
		$res = $arr1 + $arr2;
		foreach (array_intersect_key($arr1, $arr2) as $k => $v) {
			if (is_array($v) && is_array($arr2[$k])) {
				$res[$k] = self::arrayAppend($v, $arr2[$k]);
			}
		}
		return $res;
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
	 * @param  bool  stop on first error?
	 * @return void
	 */
	public function validate($breakOnFailure = FALSE)
	{
		if (!$this->isPopulated) {
			throw new /*::*/InvalidStateException('Form was not populated yet. Call method isSubmitted() or setDefaults().');
		}

		$controls = $this->getControls();

		$this->valid = TRUE;
		foreach ($controls as $control) {
			if (!$control->getRules()->validate()) {
				$this->valid = FALSE;
				if ($breakOnFailure) break;
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
	 * @return Nette::Web::Html
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
		echo call_user_func_array(array($this->getRenderer(), 'render'), $args);
	}



	/**
	 * Renders form to string.
	 * @return string
	 */
	public function __toString()
	{
		try {
			return $this->getRenderer()->render($this);
		} catch (Exception $e) {
			trigger_error($e->getMessage(), E_USER_WARNING);
			return '';
		}
	}

}
