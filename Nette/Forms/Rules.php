<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette;


/**
 * List of validation & condition rules.
 *
 * @author     David Grudl
 */
class Rules extends Nette\Object implements \IteratorAggregate
{
	/** @internal */
	const VALIDATE_PREFIX = 'validate';

	/** @var array */
	public static $defaultMessages = array(
		Form::PROTECTION => 'Please submit this form again (security token has expired).',
		Form::EQUAL => 'Please enter %s.',
		Form::NOT_EQUAL => 'This value should not be %s.',
		Form::FILLED => 'This field is required.',
		Form::BLANK => 'This field should be blank.',
		Form::MIN_LENGTH => 'Please enter at least %d characters.',
		Form::MAX_LENGTH => 'Please enter no more than %d characters.',
		Form::LENGTH => 'Please enter a value between %d and %d characters long.',
		Form::EMAIL => 'Please enter a valid email address.',
		Form::URL => 'Please enter a valid URL.',
		Form::INTEGER => 'Please enter a valid integer.',
		Form::FLOAT => 'Please enter a valid number.',
		Form::RANGE => 'Please enter a value between %d and %d.',
		Form::MAX_FILE_SIZE => 'The size of the uploaded file can be up to %d bytes.',
		Form::MAX_POST_SIZE => 'The uploaded data exceeds the limit of %d bytes.',
		Form::IMAGE => 'The uploaded file must be image in format JPEG, GIF or PNG.',
		Nette\Forms\Controls\SelectBox::VALID => 'Please select a valid option.',
	);

	/** @var Rule */
	private $required;

	/** @var Rule[] */
	private $rules = array();

	/** @var Rules */
	private $parent;

	/** @var array */
	private $toggles = array();

	/** @var IControl */
	private $control;


	public function __construct(IControl $control)
	{
		$this->control = $control;
	}


	/**
	 * Makes control mandatory.
	 * @param  mixed  state or error message
	 * @return self
	 */
	public function setRequired($value = TRUE)
	{
		if ($value) {
			$this->addRule(Form::REQUIRED, is_string($value) ? $value : NULL);
		} else {
			$this->required = NULL;
		}
		return $this;
	}


	/**
	 * Is control mandatory?
	 * @return bool
	 */
	public function isRequired()
	{
		return $this->required instanceof Rule ? !$this->required->isNegative : FALSE;
	}


	/**
	 * Adds a validation rule for the current control.
	 * @param  mixed      rule type
	 * @param  string     message to display for invalid data
	 * @param  mixed      optional rule arguments
	 * @return self
	 */
	public function addRule($operation, $message = NULL, $arg = NULL)
	{
		$rule = new Rule;
		$rule->control = $this->control;
		$rule->operation = $operation;
		$this->adjustOperation($rule);
		$rule->arg = $arg;
		$rule->type = Rule::VALIDATOR;
		$rule->message = $message;
		if ($rule->operation === Form::REQUIRED) {
			$this->required = $rule;
		} else {
			$this->rules[] = $rule;
		}
		return $this;
	}


	/**
	 * Adds a validation condition a returns new branch.
	 * @param  mixed      condition type
	 * @param  mixed      optional condition arguments
	 * @return Rules      new branch
	 */
	public function addCondition($operation, $arg = NULL)
	{
		return $this->addConditionOn($this->control, $operation, $arg);
	}


	/**
	 * Adds a validation condition on specified control a returns new branch.
	 * @param  IControl form control
	 * @param  mixed      condition type
	 * @param  mixed      optional condition arguments
	 * @return Rules      new branch
	 */
	public function addConditionOn(IControl $control, $operation, $arg = NULL)
	{
		$rule = new Rule;
		$rule->control = $control;
		$rule->operation = $operation;
		$this->adjustOperation($rule);
		$rule->arg = $arg;
		$rule->type = Rule::CONDITION;
		$rule->subRules = new static($this->control);
		$rule->subRules->parent = $this;

		$this->rules[] = $rule;
		return $rule->subRules;
	}


	/**
	 * Adds a else statement.
	 * @return Rules      else branch
	 */
	public function elseCondition()
	{
		$rule = clone end($this->parent->rules);
		$rule->isNegative = !$rule->isNegative;
		$rule->subRules = new static($this->parent->control);
		$rule->subRules->parent = $this->parent;
		$this->parent->rules[] = $rule;
		return $rule->subRules;
	}


	/**
	 * Ends current validation condition.
	 * @return Rules      parent branch
	 */
	public function endCondition()
	{
		return $this->parent;
	}


	/**
	 * Toggles HTML elememnt visibility.
	 * @param  string     element id
	 * @param  bool       hide element?
	 * @return self
	 */
	public function toggle($id, $hide = TRUE)
	{
		$this->toggles[$id] = $hide;
		return $this;
	}


	/**
	 * Validates against ruleset.
	 * @return bool
	 */
	public function validate()
	{
		foreach ($this as $rule) {
			$success = $this->validateRule($rule);

			if ($rule->type === Rule::CONDITION && $success && !$rule->subRules->validate()) {
				return FALSE;

			} elseif ($rule->type === Rule::VALIDATOR && !$success) {
				$rule->control->addError(static::formatMessage($rule, TRUE));
				return FALSE;
			}
		}
		return TRUE;
	}


	/**
	 * Validates single rule.
	 * @return bool
	 */
	public static function validateRule(Rule $rule)
	{
		$args = is_array($rule->arg) ? $rule->arg : array($rule->arg);
		foreach ($args as & $val) {
			$val = $val instanceof IControl ? $val->getValue() : $val;
		}
		return $rule->isNegative
			xor call_user_func(self::getCallback($rule), $rule->control, is_array($rule->arg) ? $args : $args[0]);
	}


	/**
	 * Iterates over complete ruleset.
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		$rules = $this->rules;
		if ($this->required) {
			array_unshift($rules, $this->required);
		}
		return new \ArrayIterator($rules);
	}


	/**
	 * @param  bool
	 * @return array
	 */
	public function getToggles($actual = FALSE)
	{
		$toggles = $this->toggles;
		foreach ($actual ? $this : array() as $rule) {
			if ($rule->type === Rule::CONDITION) {
				$success = static::validateRule($rule);
				foreach ($rule->subRules->getToggles(TRUE) as $id => $hide) {
					$toggles[$id] = empty($toggles[$id]) ? ($success && $hide) : TRUE;
				}
			}
		}
		return $toggles;
	}


	/**
	 * Process 'operation' string.
	 * @param  Rule
	 * @return void
	 */
	private function adjustOperation($rule)
	{
		if (is_string($rule->operation) && ord($rule->operation[0]) > 127) {
			$rule->isNegative = TRUE;
			$rule->operation = ~$rule->operation;
		}

		if (!is_callable($this->getCallback($rule))) {
			$operation = is_scalar($rule->operation) ? " '$rule->operation'" : '';
			throw new Nette\InvalidArgumentException("Unknown operation$operation for control '{$rule->control->name}'.");
		}
	}


	private static function getCallback($rule)
	{
		$op = $rule->operation;
		if (is_string($op) && strncmp($op, ':', 1) === 0) {
			return get_class($rule->control) . '::' . self::VALIDATE_PREFIX . ltrim($op, ':');
		} else {
			return $op;
		}
	}


	public static function formatMessage($rule, $withValue)
	{
		$message = $rule->message;
		if ($message instanceof Nette\Utils\Html) {
			return $message;

		} elseif ($message === NULL && is_string($rule->operation) && isset(self::$defaultMessages[$rule->operation])) {
			$message = self::$defaultMessages[$rule->operation];

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


}
