<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
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
	private $disabled = FALSE;

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
	}


	/**
	 * This method will be called when the component becomes attached to Form.
	 * @param  Nette\ComponentModel\IComponent
	 * @return void
	 */
	protected function attached($form)
	{
		if (!$this->disabled && $form instanceof Form && $form->isAnchored() && $form->isSubmitted()) {
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
		$this->setValue($this->getHttpData());
	}


	/**
	 * Loads HTTP data.
	 * @return void
	 */
	public function getHttpData($type = Nette\Forms\Form::DATA_TEXT, $htmlTail = NULL)
	{
		return $this->getForm()->getHttpData($this->getHtmlName() . $htmlTail, $type);
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
	 * @return BaseControl  provides a fluent interface
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
	 * @return BaseControl  provides a fluent interface
	 */
	public function setDefaultValue($value)
	{
		$form = $this->getForm(FALSE);
		if ($this->disabled || !$form || !$form->isAnchored() || !$form->isSubmitted()) {
			$this->setValue($value);
		}
		return $this;
	}


	/**
	 * Disables or enables control.
	 * @param  bool
	 * @return BaseControl  provides a fluent interface
	 */
	public function setDisabled($value = TRUE)
	{
		$this->disabled = (bool) $value;
		return $this;
	}


	/**
	 * Is control disabled?
	 * @return bool
	 */
	public function isDisabled()
	{
		return $this->disabled;
	}


	/**
	 * Sets whether control value is excluded from $form->getValues() result.
	 * @param  bool
	 * @return BaseControl  provides a fluent interface
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
		))->data('nette-rules', $rules ? Nette\Utils\Json::encode($rules) : NULL);
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
	final public function getControlPrototype()
	{
		return $this->control;
	}


	/**
	 * Returns label's HTML element template.
	 * @return Nette\Utils\Html
	 */
	final public function getLabelPrototype()
	{
		return $this->label;
	}


	/**
	 * Changes control's HTML id.
	 * @param  string new ID, or FALSE or NULL
	 * @return BaseControl  provides a fluent interface
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
	 * @return BaseControl  provides a fluent interface
	 */
	public function setAttribute($name, $value = TRUE)
	{
		$this->control->$name = $value;
		return $this;
	}


	/********************* translator ****************d*g**/


	/**
	 * Sets translate adapter.
	 * @return BaseControl  provides a fluent interface
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
		if ($this->translator === TRUE) {
			return $this->getForm(FALSE) ? $this->getForm()->getTranslator() : NULL;
		}
		return $this->translator;
	}


	/**
	 * Returns translated string.
	 * @param  string
	 * @param  int      plural count
	 * @return string
	 */
	public function translate($s, $count = NULL)
	{
		$translator = $this->getTranslator();
		return $translator === NULL || $s == NULL || $s instanceof Html  // intentionally ==
			? $s
			: $translator->translate((string) $s, $count);
	}


	/********************* rules ****************d*g**/


	/**
	 * Adds a validation rule.
	 * @param  mixed      rule type
	 * @param  string     message to display for invalid data
	 * @param  mixed      optional rule arguments
	 * @return BaseControl  provides a fluent interface
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
	final public function getRules()
	{
		return $this->rules;
	}


	/**
	 * Makes control mandatory.
	 * @param  mixed  state or error message
	 * @return BaseControl  provides a fluent interface
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
	final public function isRequired()
	{
		return $this->rules->isRequired();
	}


	/**
	 * Performs the server side validation.
	 * @return void
	 */
	public function validate()
	{
		$this->cleanErrors();
		foreach ($this->rules->validate() as $error) {
			$this->addError($error);
		}
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


	public function formatMessage($rule, $withValue = TRUE)
	{
		$message = $rule->message;
		if ($message instanceof Nette\Utils\Html) {
			return $message;

		} elseif ($message === NULL && is_string($rule->operation) && isset(Nette\Forms\Rules::$defaultMessages[$rule->operation])) {
			$message = Nette\Forms\Rules::$defaultMessages[$rule->operation];

		} elseif ($message == NULL) { // intentionally ==
			trigger_error("Missing validation message for control '{$rule->control->name}'.", E_USER_WARNING);
		}

		if ($translator = $rule->control->getForm()->getTranslator()) {
			$message = $translator->translate($message, is_int($rule->arg) ? $rule->arg : NULL);
		}

		$message = preg_replace_callback('#%(name|label|value|\d+\$[ds]|[ds])#', function($m) use ($rule, $withValue) {
			static $i = -1;
			switch ($m[1]) {
				case 'name': return $rule->control->getName();
				case 'label': return $rule->control->translate($rule->control->caption);
				case 'value': return $withValue ? $rule->control->getValue() : $m[0];
				default:
					$args = is_array($rule->arg) ? $rule->arg : array($rule->arg);
					$i = (int) $m[1] ? $m[1] - 1 : $i + 1;
					return isset($args[$i]) ? ($args[$i] instanceof IControl ? ($withValue ? $args[$i]->getValue() : "%$i") : $args[$i]) : '';
			}
		}, $message);
		return $message;
	}


	/**
	 * @return array
	 */
	protected static function exportRules($rules)
	{
		$payload = array();
		foreach ($rules as $rule) {
			if (!is_string($op = $rule->operation)) {
				$op = new Nette\Callback($op);
				if (!$op->isStatic()) {
					continue;
				}
			}
			if ($rule->type === Rule::VALIDATOR) {
				$item = array('op' => ($rule->isNegative ? '~' : '') . $op, 'msg' => $rule->control->formatMessage($rule, FALSE));

			} elseif ($rule->type === Rule::CONDITION) {
				$item = array(
					'op' => ($rule->isNegative ? '~' : '') . $op,
					'rules' => self::exportRules($rule->subRules),
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


	/********************* user data ****************d*g**/


	/**
	 * Sets user-specific option.
	 * @return BaseControl  provides a fluent interface
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
	final public function getOption($key, $default = NULL)
	{
		return isset($this->options[$key]) ? $this->options[$key] : $default;
	}


	/**
	 * Returns user-specific options.
	 * @return array
	 */
	final public function getOptions()
	{
		return $this->options;
	}

}
