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
 * @package    Nette::Forms
 */

/*namespace Nette::Forms;*/



require_once dirname(__FILE__) . '/../Forms/FormContainer.php';



/**
 * Form - allows create, validate and render (X)HTML forms.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Forms
 * @version    $Revision$ $Date$
 */
class Form extends FormContainer
{
	/** Deprecated constants. */
	const EQUAL = ':Equal';
	const FILLED = ':Filled';

	const SUBMITTED = /*Nette::Forms::*/'SubmitButton::validateSubmitted';

	const MIN_LENGTH = /*Nette::Forms::*/'TextBase::validateMinLength';
	const MAX_LENGTH = /*Nette::Forms::*/'TextBase::validateMaxLength';
	const LENGTH = /*Nette::Forms::*/'TextBase::validateLength';
	const EMAIL = /*Nette::Forms::*/'TextBase::validateEmail';
	const URL = /*Nette::Forms::*/'TextBase::validateUrl';
	const REGEXP = /*Nette::Forms::*/'TextBase::validateRegexp';
	const NUMERIC = /*Nette::Forms::*/'TextBase::validateNumeric';
	const FLOAT = /*Nette::Forms::*/'TextBase::validateFloat';
	const RANGE = /*Nette::Forms::*/'TextBase::validateRange';

	const MAX_FILE_SIZE = /*Nette::Forms::*/'FileUpload::validateFileSize';
	const MIME_TYPE = '/*Nette::Forms::*/FileUpload::validateMimeType';


	/** Tracker ID */
	const TRACKER_ID = '_form_';

	/** @var array - function($sender, $submittor) */
	public $onSubmit;

	/** @var bool  fire event onSubmit only for valid form? */
	public $onlyValid = TRUE;

	/** @var bool */
	protected $isPost = TRUE;

	/** @var mixed */
	protected $submittedBy;

	/** @var Html  <form> element */
	private $element;

	/** @var ITranslator */
	private $translator;

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
		parent::__construct($parent, $name);
	}



	protected function notification(/*Nette::*/IComponent $sender, $message)
	{
		if ($message === self::HIERARCHY_ATTACH && $this->getParent()->lookup(__CLASS__)) {
			throw new /*::*/InvalidStateException('Nested forms are forbidden.');
		}
		parent::notification($sender, $message);
	}



	/**
	 * Sets form's action and method.
	 * @param  string URI
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
	 * @return HiddenField
	 */
	public function addTracker($name)
	{
		// TODO: implement Cross-Site Request Forgery token
		$this[self::TRACKER_ID] = new HiddenField;
		$this[self::TRACKER_ID]->setValue($name);
	}



	/********************* translator ****************d*g**/



	/**
	 * Sets translate adapter.
	 * @param  ITranslator
	 * @return void
	 */
	public function setTranslator($translator = NULL)
	{
		$this->translator = $translator;
	}



	/**
	 * Returns translate adapter.
	 * @return ITranslator
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

		// standalone mode
		if ($this->isPost xor @$_SERVER['REQUEST_METHOD'] === 'POST') return;

		$tracker = $this->getComponent(self::TRACKER_ID);
		if ($tracker) {
			if ($this->isPost) {
				if (!isset($_POST[self::TRACKER_ID])) return;
				if ($_POST[self::TRACKER_ID] !== $tracker->getValue()) return;
			} else {
				if (!isset($_GET[self::TRACKER_ID])) return;
				if ($_GET[self::TRACKER_ID] !== $tracker->getValue()) return;
			}
		}

		$this->submittedBy = TRUE;
		$this->loadHttpData();
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
				$control->setValue(isset($sub->cursor[$name]) ? $sub->cursor[$name] : NULL);
			}
			if ($control instanceof INamingContainer) {
				$cursor = & $sub->cursor[$name];
				if (!is_array($cursor)) $cursor = array(); // note: modifies data
			}
		}
		$this->isPopulated = TRUE;
	}



	/**
	 * Fill-in the form with HTTP data. Doesn't check if form was submitted.
	 * @param  array    user data
	 * @return void
	 */
	public function loadHttpData(array $data = NULL)
	{
		if ($data === NULL) {
			$data = $this->isPost ? $_POST + $_FILES : $_GET;
		}

		$cursor = & $data;
		$iterator = $this->getComponents(TRUE);
		foreach ($iterator as $name => $control) {
			$sub = $iterator->getSubIterator();
			if (!isset($sub->cursor)) {
				$sub->cursor = & $cursor;
			}
			if ($control instanceof IFormControl && !$control->getDisabled()) {
				$control->loadHttpData($sub->cursor);
				if ($control instanceof ISubmitterControl && ($this->submittedBy === TRUE || $control->isSubmittedBy())) {
					$this->submittedBy = $control;
				}
			}
			if ($control instanceof INamingContainer) {
				$cursor = & $sub->cursor[$name];
				if (!is_array($cursor)) $cursor = array(); // note: modifies data
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
			if ($control instanceof IFormControl && !$control->getDisabled() && !($control instanceof ISubmitterControl)) {
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



	/********************* rules ****************d*g**/



	/**
	 * Adds a validation rule for the control. Deprecated.
	 * @param  string     form control name
	 * @param  mixed      rule type
	 * @param  string     message to display for invalid data
	 * @param  mixed      optional extra rule data
	 * @return void
	 * @deprecated
	 */
	public function addRule($name, $operation, $message, $arg = NULL)
	{
		trigger_error("Deprecated: use \$form['$name']->addRule(...) instead.", E_USER_NOTICE);
		$this->getComponent($name, TRUE)->addRule($operation, $message, $arg);
	}



	/**
	 * Adds a validation condition. Deprecated
	 * @param  string     form control name
	 * @param  mixed      condition type
	 * @param  mixed      optional condition data
	 * @param  string     optional HTML #ID to be toggled
	 * @return Rules
	 * @deprecated
	 */
	public function addCondition($name, $operation, $value = NULL, $toggle = NULL)
	{
		trigger_error("Deprecated: use \$form['$name']->addCondition(...) instead.", E_USER_NOTICE);
		$cond = $this->getComponent($name, TRUE)->addCondition($operation, $value, $toggle);
		if ($toggle) $cond->toggle($toggle);
		return $cond;
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
	 * @param  bool
	 * @return void
	 */
	public function validate($stopOnFirst = FALSE)
	{
		if (!$this->isPopulated) {
			throw new /*::*/InvalidStateException('Form was not populated yet. Call method isSubmitted() or setDefaults().');
		}
		$this->valid = TRUE;
		foreach ($this->getComponents(TRUE, 'Nette::Forms::IFormControl') as $control) {
			if (!$control->getRules()->validate()) {
				$this->valid = FALSE;
				if ($stopOnFirst) break;
			}
		}

		if (!$this->valid) {
			foreach ($this->getComponents(TRUE, 'Nette::Forms::IFormControl') as $control) {
				$this->errors = array_merge($this->errors, $control->getErrors());
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
	 * Provides complete form rendering.
	 * @return void
	 */
	public function renderForm()
	{
		// TODO:
		//$js = new InstantClientScript($this);
		//$js->enable();

		$this->renderBegin();
		if ($this->submittedBy) {
			$this->renderErrors();
		}
		$this->renderBody();
		$this->renderEnd();
		//$js->renderClientScript();
	}



	private $js;

	/**
	 * Renders form's start tag.
	 * @return void
	 */
	public function renderBegin()
	{
		$this->js = new InstantClientScript($this);
		$this->js->enable();
		echo $this->element->startTag();
	}



	/**
	 * Renders the rest of the form.
	 * @return void
	 */
	public function renderEnd()
	{
		echo $this->element->endTag();
		$this->js->renderClientScript();
	}



	/**
	 * Renders validation errors (per form or per control).
	 * @param  IFormControl
	 * @return void
	 */
	public function renderErrors($control = NULL)
	{
		$errors = $control ? $control->getErrors() : $this->getErrors();
		if (count($errors)) {
			$ul = /*Nette::Web::*/Html::el('ul')->class('error');
			foreach ($errors as $error) {
				$ul->create('li', $error);
			}
			echo "\n", $ul;
		}
	}



	/**
	 * Renders form body.
	 * @param  FormContainer
	 * @return void
	 */
	public function renderBody()
	{
		// TODO: implement some decorators
		echo "\n<table>\n";
		$hidden = /*Nette::Web::*/Html::el('div');
		foreach ($this->getComponents(TRUE) as $control) {
			if ($control instanceof HiddenField) {
				$hidden->add($control->getControl());

			} elseif ($control instanceof Checkbox) {
				echo "<tr>\n\t<th>&nbsp;</th>\n\t<td>", $control->control, $control->label, "</td>\n</tr>\n\n";

			} elseif ($control instanceof FormControl && !$control->isRendered()) {
				echo "<tr>\n\t<th>", ($control->label ? $control->label : '&nbsp;'), "</th>\n\t<td>", $control->control, "</td>\n</tr>\n\n";
			}
		}
		echo "</table>\n";
		if (count($hidden)) echo $hidden;
	}

}
