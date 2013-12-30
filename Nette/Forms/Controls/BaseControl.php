<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette,
	Nette\Forms\IControl,
	Nette\Utils\Html,
	Nette\Forms\Form,
	Nette\Forms\Rule;


/**
 * Base class that implements the basic functionality common to form controls.
 *
 * @author     David Grudl
 *
 * @property-read Nette\Forms\Form $form
 * @property-read string $htmlName
 * @property   string $htmlId
 * @property-read array $options
 * @property   Nette\Localization\ITranslator|NULL $translator
 * @property   mixed $value
 * @property-read bool $filled
 * @property-write $defaultValue
 * @property   bool $disabled
 * @property   bool $omitted
 * @property-read Nette\Utils\Html $control
 * @property-read Nette\Utils\Html $label
 * @property-read Nette\Utils\Html $controlPrototype
 * @property-read Nette\Utils\Html $labelPrototype
 * @property-read Nette\Forms\Rules $rules
 * @property   bool $required
 * @property-read array $errors
 */
abstract class BaseControl extends Nette\ComponentModel\Component implements IControl
{
	/** @var string */
	public static $idMask = 'frm-%s';

	/** @var string textual caption or label */
	public $caption;

	/** @var mixed current control value */
	protected $value;

	/** @var Nette\Utils\Html  control element template */
	protected $control;

	/** @var Nette\Utils\Html  label element template */
	protected $label;

	/** @var array */
	private $errors = array();

	/** @var bool */
	protected $disabled = FALSE;

	/** @var bool */
	private $omitted = FALSE;

	/** @var Nette\Forms\Rules */
	private $rules;

	/** @var Nette\Localization\ITranslator */
	private $translator = TRUE; // means autodetect

	/** @var array user options */
	private $options = array();


	/**
	 * @param  string  caption
	 */
	public function __construct($caption = NULL)
	{
		$this->monitor('Nette\Forms\Form');
		parent::__construct();
		$this->control = Html::el('input', array('type' => NULL, 'name' => NULL));
		$this->label = Html::el('label');
		$this->caption = $caption;
		$this->rules = new Nette\Forms\Rules($this);
		$this->setValue(NULL);
	}


	/**
	 * This method will be called when the component becomes attached to Form.
	 * @param  Nette\ComponentModel\IComponent
	 * @return void
	 */
	protected function attached($form)
	{
		if (!$this->isDisabled() && $form instanceof Form && $form->isAnchored() && $form->isSubmitted()) {
			$this->loadHttpData();
		}
	}


	/**
	 * Returns form.
	 * @param  bool   throw exception if form doesn't exist?
	 * @return Nette\Forms\Form
	 */
	public function getForm($need = TRUE)
	{
		return $this->lookup('Nette\Forms\Form', $need);
	}


	/**
	 * Loads HTTP data.
	 * @return void
	 */
	public function loadHttpData()
	{
		$this->setValue($this->getHttpData(Form::DATA_TEXT));
	}


	/**
	 * Loads HTTP data.
	 * @return mixed
	 */
	public function getHttpData($type, $htmlTail = NULL)
	{
		return $this->getForm()->getHttpData($type, $this->getHtmlName() . $htmlTail);
	}


	/**
	 * Returns HTML name of control.
	 * @return string
	 */
	public function getHtmlName()
	{
		return Nette\Forms\Helpers::generateHtmlName($this->lookupPath('Nette\Forms\Form'));
	}


	/********************* interface IFormControl ****************d*g**/


	/**
	 * Sets control's value.
	 * @return self
	 */
	public function setValue($value)
	{
		$this->value = $value;
		return $this;
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
	 * Is control filled?
	 * @return bool
	 */
	public function isFilled()
	{
		$value = $this->getValue();
		return $value !== NULL && $value !== array() && $value !== '';
	}


	/**
	 * Sets control's default value.
	 * @return self
	 */
	public function setDefaultValue($value)
	{
		$form = $this->getForm(FALSE);
		if ($this->isDisabled() || !$form || !$form->isAnchored() || !$form->isSubmitted()) {
			$this->setValue($value);
		}
		return $this;
	}


	/**
	 * Disables or enables control.
	 * @param  bool
	 * @return self
	 */
	public function setDisabled($value = TRUE)
	{
		if ($this->disabled = (bool) $value) {
			$this->omitted = TRUE;
			$this->setValue(NULL);
		}
		return $this;
	}


	/**
	 * Is control disabled?
	 * @return bool
	 */
	public function isDisabled()
	{
		return $this->disabled === TRUE;
	}


	/**
	 * Sets whether control value is excluded from $form->getValues() result.
	 * @param  bool
	 * @return self
	 */
	public function setOmitted($value = TRUE)
	{
		$this->omitted = (bool) $value;
		return $this;
	}


	/**
	 * Is control value excluded from $form->getValues() result?
	 * @return bool
	 */
	public function isOmitted()
	{
		return $this->omitted;
	}


	/********************* rendering ****************d*g**/


	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$this->setOption('rendered', TRUE);

		$rules = self::exportRules($this->rules);
		$el = clone $this->control;
		return $el->addAttributes(array(
			'name' => $this->getHtmlName(),
			'id' => $this->getHtmlId(),
			'required' => $this->isRequired(),
			'disabled' => $this->isDisabled(),
			'data-nette-rules' => $rules ? Nette\Utils\Json::encode($rules) : NULL,
		));
	}


	/**
	 * Generates label's HTML element.
	 * @param  string
	 * @return Nette\Utils\Html
	 */
	public function getLabel($caption = NULL)
	{
		$label = clone $this->label;
		$label->for = $this->getHtmlId();
		$label->setText($this->translate($caption === NULL ? $this->caption : $caption));
		return $label;
	}


	/**
	 * Returns control's HTML element template.
	 * @return Nette\Utils\Html
	 */
	public function getControlPrototype()
	{
		return $this->control;
	}


	/**
	 * Returns label's HTML element template.
	 * @return Nette\Utils\Html
	 */
	public function getLabelPrototype()
	{
		return $this->label;
	}


	/**
	 * Changes control's HTML id.
	 * @param  string new ID, or FALSE or NULL
	 * @return self
	 */
	public function setHtmlId($id)
	{
		$this->control->id = $id;
		return $this;
	}


	/**
	 * Returns control's HTML id.
	 * @return string
	 */
	public function getHtmlId()
	{
		if (!isset($this->control->id)) {
			$this->control->id = sprintf(self::$idMask, $this->lookupPath(NULL));
		}
		return $this->control->id;
	}


	/**
	 * Changes control's HTML attribute.
	 * @param  string name
	 * @param  mixed  value
	 * @return self
	 */
	public function setAttribute($name, $value = TRUE)
	{
		$this->control->$name = $value;
		return $this;
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
		if ($this->translator === TRUE) {
			return $this->getForm(FALSE) ? $this->getForm()->getTranslator() : NULL;
		}
		return $this->translator;
	}


	/**
	 * Returns translated string.
	 * @param  mixed
	 * @param  int      plural count
	 * @return string
	 */
	public function translate($value, $count = NULL)
	{
		if ($translator = $this->getTranslator()) {
			$tmp = is_array($value) ? array(& $value) : array(array(& $value));
			foreach ($tmp[0] as & $v) {
				if ($v != NULL && !$v instanceof Html) { // intentionally ==
					$v = $translator->translate((string) $v, $count);
				}
			}
		}
		return $value;
	}


	/********************* rules ****************d*g**/


	/**
	 * Adds a validation rule.
	 * @param  mixed      rule type
	 * @param  string     message to display for invalid data
	 * @param  mixed      optional rule arguments
	 * @return self
	 */
	public function addRule($operation, $message = NULL, $arg = NULL)
	{
		$this->rules->addRule($operation, $message, $arg);
		return $this;
	}


	/**
	 * Adds a validation condition a returns new branch.
	 * @param  mixed     condition type
	 * @param  mixed     optional condition arguments
	 * @return Nette\Forms\Rules      new branch
	 */
	public function addCondition($operation, $value = NULL)
	{
		return $this->rules->addCondition($operation, $value);
	}


	/**
	 * Adds a validation condition based on another control a returns new branch.
	 * @param  Nette\Forms\IControl form control
	 * @param  mixed      condition type
	 * @param  mixed      optional condition arguments
	 * @return Nette\Forms\Rules      new branch
	 */
	public function addConditionOn(IControl $control, $operation, $value = NULL)
	{
		return $this->rules->addConditionOn($control, $operation, $value);
	}


	/**
	 * @return Nette\Forms\Rules
	 */
	public function getRules()
	{
		return $this->rules;
	}


	/**
	 * Makes control mandatory.
	 * @param  mixed  state or error message
	 * @return self
	 */
	public function setRequired($value = TRUE)
	{
		$this->rules->setRequired($value);
		return $this;
	}


	/**
	 * Is control mandatory?
	 * @return bool
	 */
	public function isRequired()
	{
		return $this->rules->isRequired();
	}


	/**
	 * Performs the server side validation.
	 * @return void
	 */
	public function validate()
	{
		if ($this->isDisabled()) {
			return;
		}
		$this->cleanErrors();
		$this->rules->validate();
	}


	/**
	 * Adds error message to the list.
	 * @param  string  error message
	 * @return void
	 */
	public function addError($message)
	{
		$this->errors[] = $message;
	}


	/**
	 * Returns errors corresponding to control.
	 * @return string
	 */
	public function getError()
	{
		return $this->errors ? implode(' ', array_unique($this->errors)) : NULL;
	}


	/**
	 * Returns errors corresponding to control.
	 * @return array
	 */
	public function getErrors()
	{
		return array_unique($this->errors);
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


	/**
	 * @return array
	 */
	protected static function exportRules($rules)
	{
		$payload = array();
		foreach ($rules as $rule) {
			if (!is_string($op = $rule->operation)) {
				if (!Nette\Utils\Callback::isStatic($op)) {
					continue;
				}
				$op = Nette\Utils\Callback::toString($op);
			}
			if ($rule->type === Rule::VALIDATOR) {
				$item = array('op' => ($rule->isNegative ? '~' : '') . $op, 'msg' => $rules->formatMessage($rule, FALSE));

			} elseif ($rule->type === Rule::CONDITION) {
				$item = array(
					'op' => ($rule->isNegative ? '~' : '') . $op,
					'rules' => static::exportRules($rule->subRules),
					'control' => $rule->control->getHtmlName()
				);
				if ($rule->subRules->getToggles()) {
					$item['toggle'] = $rule->subRules->getToggles();
				}
			}

			if (is_array($rule->arg)) {
				foreach ($rule->arg as $key => $value) {
					$item['arg'][$key] = $value instanceof IControl ? array('control' => $value->getHtmlName()) : $value;
				}
			} elseif ($rule->arg !== NULL) {
				$item['arg'] = $rule->arg instanceof IControl ? array('control' => $rule->arg->getHtmlName()) : $rule->arg;
			}

			$payload[] = $item;
		}
		return $payload;
	}


	/********************* validators ****************d*g**/


	/**
	 * Equal validator: are control's value and second parameter equal?
	 * @return bool
	 */
	public static function validateEqual(IControl $control, $arg)
	{
		$value = $control->getValue();
		foreach ((is_array($value) ? $value : array($value)) as $val) {
			foreach ((is_array($arg) ? $arg : array($arg)) as $item) {
				if ((string) $val === (string) $item) {
					continue 2;
				}
			}
			return FALSE;
		}
		return TRUE;
	}


	/**
	 * Is control's value not equal with second parameter?
	 * @return bool
	 */
	public static function validateNotEqual(IControl $control, $arg)
	{
		return !static::validateEqual($control, $arg);
	}


	/**
	 * Filled validator: is control filled?
	 * @return bool
	 */
	public static function validateFilled(IControl $control)
	{
		return $control->isFilled();
	}


	/**
	 * Is control not filled?
	 * @return bool
	 */
	public static function validateBlank(IControl $control)
	{
		return !$control->isFilled();
	}


	/**
	 * Valid validator: is control valid?
	 * @return bool
	 */
	public static function validateValid(IControl $control)
	{
		return $control->rules->validate();
	}


	/**
	 * Rangle validator: is a control's value number in specified range?
	 * @param  Nette\Forms\IControl
	 * @param  array  min and max value pair
	 * @return bool
	 */
	public static function validateRange(IControl $control, $range)
	{
		return Nette\Utils\Validators::isInRange($control->getValue(), $range);
	}


	/**
	 * Count/length validator. Range is array, min and max length pair.
	 * @return bool
	 */
	public static function validateLength(IControl $control, $range)
	{
		if (!is_array($range)) {
			$range = array($range, $range);
		}
		$value = $control->getValue();
		return Nette\Utils\Validators::isInRange(is_array($value) ? count($value) : Nette\Utils\Strings::length($value), $range);
	}


	/**
	 * Min-length validator: has control's value minimal count/length?
	 * @return bool
	 */
	public static function validateMinLength(IControl $control, $length)
	{
		return static::validateLength($control, array($length, NULL));
	}


	/**
	 * Max-length validator: is control's value count/length in limit?
	 * @return bool
	 */
	public static function validateMaxLength(IControl $control, $length)
	{
		return static::validateLength($control, array(NULL, $length));
	}


	/********************* user data ****************d*g**/


	/**
	 * Sets user-specific option.
	 * @return self
	 */
	public function setOption($key, $value)
	{
		if ($value === NULL) {
			unset($this->options[$key]);
		} else {
			$this->options[$key] = $value;
		}
		return $this;
	}


	/**
	 * Returns user-specific option.
	 * @return mixed
	 */
	public function getOption($key, $default = NULL)
	{
		return isset($this->options[$key]) ? $this->options[$key] : $default;
	}


	/**
	 * Returns user-specific options.
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}

}
