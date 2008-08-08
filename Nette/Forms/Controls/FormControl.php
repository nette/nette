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



require_once dirname(__FILE__) . '/../../Component.php';

require_once dirname(__FILE__) . '/../../Forms/IFormControl.php';



/**
 * Base class that implements the basic functionality common to form controls.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Forms
 * @version    $Revision$ $Date$
 */
abstract class FormControl extends /*Nette::*/Component implements IFormControl
{
	/** @var string */
	public static $idMask = 'frm%f-%n';

	/** @var mixed */
	protected $value;

	/** @var Nette::Web::Html  control element template */
	protected $control;

	/** @var Nette::Web::Html  label element template */
	protected $label;

	/** @var array */
	private $errors = array();

	/** @var bool */
	private $disabled = FALSE;

	/** @var bool */
	private $rendered = FALSE;

	/** @var string */
	private $htmlId;

	/** @var string */
	private $htmlName;

	/** @var Rules */
	private $rules;

	/** @var ITranslator */
	private $translator;



	/**
	 * @param  string  label
	 */
	public function __construct($label)
	{
		parent::__construct();
		$this->control = /*Nette::Web::*/Html::el('input');
		$this->label = /*Nette::Web::*/Html::el('label')->setText($label);
		$this->rules = new Rules($this);
	}



	/**
	 * Overloaded parent setter. This method checks for invalid control name.
	 * @param  IComponentContainer
	 * @param  string
	 * @return void
	 */
	public function setParent(/*Nette::*/IComponentContainer $parent = NULL, $name = NULL)
	{
		if ($name === 'submit') {
			throw new /*::*/InvalidArgumentException("Name 'submit' is not allowed due to JavaScript limitations.");
		}
		parent::setParent($parent, $name);
	}



	/**
	 * Returns form.
	 * @param  bool   throw exception if form doesn't exist?
	 * @return Form
	 */
	public function getForm($need = TRUE)
	{
		return $this->lookup('Nette::Forms::Form', $need);
	}



	/**
	 * Returns name of control within a Form & INamingContainer scope.
	 * @return string
	 */
	public function getHtmlName()
	{
		if ($this->htmlName === NULL) {
			$s = '';
			$name = $this->getName();
			$obj = $this->lookup('Nette::Forms::INamingContainer', TRUE);
			while (!($obj instanceof Form)) {
				$s = "[$name]$s";
				$name = $obj->getName();
				$obj = $obj->getParent()->lookup('Nette::Forms::INamingContainer', TRUE);
			}
			$this->htmlName = "$name$s";
		}
		return $this->htmlName;
	}



	/**
	 * Changes control's HTML id.
	 * @param  string new ID, or FALSE or NULL
	 * @return void
	 */
	public function setHtmlId($id)
	{
		$this->htmlId = $id;
	}



	/**
	 * Returns control's HTML id.
	 * @return string
	 */
	public function getHtmlId()
	{
		if ($this->htmlId === FALSE) {
			return NULL;

		} elseif ($this->htmlId === NULL) {
			$this->htmlId = str_replace(
				array('%f', '%n'),
				array($this->getForm()->getName(), $this->getHtmlName()),
				self::$idMask
			);
			$this->htmlId = str_replace(array('[', ']'), array('-', ''), $this->htmlId);
		}
		return $this->htmlId;
	}



	/********************* translator ****************d*g**/



	/**
	 * Sets translate adapter.
	 * @param  ITranslator or FALSE disables translator
	 * @return void
	 */
	public function setTranslator($translator)
	{
		$this->translator = $translator;
	}



	/**
	 * Returns translate adapter.
	 * @return ITranslator|NULL
	 */
	final public function getTranslator()
	{
		if ($this->translator === FALSE) {
			return NULL;

		} elseif ($this->translator === NULL) {
			return $this->getForm()->getTranslator();

		} else {
			return $this->translator;
		}
	}



	/********************* interface IFormControl ****************d*g**/



	/**
	 * Sets control's value.
	 * @param  mixed
	 * @return void
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}



	/**
	 * Returns control's value.
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}



	/**
	 * Loads HTTP data.
	 * @param  array
	 * @return void
	 */
	public function loadHttpData($data)
	{
		$name = $this->getName();
		$this->setValue(isset($data[$name]) ? $data[$name] : NULL);
	}



	/**
	 * Disables or enables control.
	 * @param  bool
	 * @return FormControl  provides a fluent interface
	 */
	public function setDisabled($value = TRUE)
	{
		$this->disabled = $value;
		return $this;
	}



	/**
	 * Is control disabled?
	 * @return bool
	 */
	public function getDisabled()
	{
		return $this->disabled;
	}



	/********************* rendering ****************d*g**/



	/**
	 * Generates control's HTML element.
	 * @return Nette::Web::Html
	 */
	public function getControl()
	{
		$this->rendered = TRUE;
		$control = clone $this->control;
		$control->name = $this->getHtmlName();
		$control->disabled = $this->disabled;
		$control->id = $this->getHtmlId();
		return $control;
	}



	/**
	 * Generates label's HTML element.
	 * @return Nette::Web::Html
	 */
	public function getLabel()
	{
		$label = clone $this->label;
		$label->for = $this->getHtmlId();
		if ($this->translator !== NULL) {
			$label->setText($this->translator->translate($label->getText()));
		}
		return $label;
	}



	/**
	 * Returns control's HTML element template.
	 * @return Nette::Web::Html
	 */
	final public function getControlPrototype()
	{
		return $this->control;
	}



	/**
	 * Returns label's HTML element template.
	 * @return Nette::Web::Html
	 */
	final public function getLabelPrototype()
	{
		return $this->label;
	}



	/**
	 * Does method getControl() have been called?
	 * @param  bool  optional new value
	 * @return bool
	 */
	public function isRendered($value = NULL)
	{
		if ($value !== NULL) $this->rendered = (bool) $value;
		return $this->rendered;
	}



	/********************* rules ****************d*g**/



	/**
	 * Adds a validation rule.
	 * @param  mixed      rule type
	 * @param  string     message to display for invalid data
	 * @param  mixed      optional rule arguments
	 * @return FormContainer  provides a fluent interface
	 */
	public function addRule($operation, $message = NULL, $arg = NULL)
	{
		$this->rules->addRule($operation, $message, $arg);
		return $this;
	}



	/**
	 * Adds a validation condition a returns new branch.
	 * @param  mixed     condition type
	 * @param  mixed      optional condition arguments
	 * @return Rules      new branch
	 */
	public function addCondition($operation, $value = NULL)
	{
		return $this->rules->addCondition($operation, $value);
	}



	/**
	 * Adds a validation condition based on another control a returns new branch.
	 * @param  IFormControl form control
	 * @param  mixed      condition type
	 * @param  mixed      optional condition arguments
	 * @return Rules      new branch
	 */
	public function addConditionOn(IFormControl $control, $operation, $value = NULL)
	{
		return $this->rules->addConditionOn($control, $operation, $value);
	}



	/**
	 * @return Rules
	 */
	final public function getRules()
	{
		return $this->rules;
	}



	/**
	 * Makes control mandatory.
	 * @param  string  error message
	 * @return FormControl  provides a fluent interface
	 */
	final public function setRequired($message = NULL)
	{
		$this->rules->addRule(':Filled', $message);
		return $this;
	}



	/**
	 * New rule or condition notification callback.
	 * @param  Rule
	 * @return void
	 */
	public function notifyRule(Rule $rule)
	{
		if (is_string($rule->operation)) {
			// TODO: too complicated
			$op = strrchr($rule->operation, ':');
			$class = substr($rule->operation, 0, -strlen($op) - 1);
			if (!$rule->isCondition && strcasecmp($op, ':validateFilled') === 0 && is_subclass_of($class, __CLASS__)) {
				$this->label->class[] = 'required'; // TODO: resit jinak
			}
		}
	}



	/********************* validation ****************d*g**/



	/**
	 * Equal validator: are control's value and second parameter equal?
	 * @param  IFormControl
	 * @param  mixed
	 * @return bool
	 */
	public static function validateEqual(IFormControl $control, $arg)
	{
		if (is_object($arg)) {
			return get_class($arg) === get_class($control) ? $control->getValue() == $arg->value : FALSE;

		} else {
			return $control->getValue() == $arg;
		}
	}



	/**
	 * Filled validator: is control filled?
	 * @param  IFormControl
	 * @return bool
	 */
	public static function validateFilled(IFormControl $control)
	{
		return (string) $control->getValue() !== ''; // NULL, FALSE, '' ==> FALSE
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
	 * Returns errors corresponding to control.
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
		return (bool) $this->errors;
	}



	/**
	 * @return void
	 */
	public function cleanErrors()
	{
		$this->errors = array();
	}

}
