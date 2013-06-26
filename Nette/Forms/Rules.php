<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Forms;

use Nette;



/**
 * List of validation & condition rules.
 *
 * @author     David Grudl
 */
final class Rules extends Nette\Object implements \IteratorAggregate
{
	/** @internal */
	const VALIDATE_PREFIX = 'validate';

	/** @var array */
	public static $defaultMessages = array(
		Form::PROTECTION => 'Please submit this form again (security token has expired).',
		Form::EQUAL => 'Please enter %s.',
		Form::FILLED => 'Please complete mandatory field.',
		Form::MIN_LENGTH => 'Please enter a value of at least %d characters.',
		Form::MAX_LENGTH => 'Please enter a value no longer than %d characters.',
		Form::LENGTH => 'Please enter a value between %d and %d characters long.',
		Form::EMAIL => 'Please enter a valid email address.',
		Form::URL => 'Please enter a valid URL.',
		Form::INTEGER => 'Please enter a numeric value.',
		Form::FLOAT => 'Please enter a numeric value.',
		Form::RANGE => 'Please enter a value between %d and %d.',
		Form::MAX_FILE_SIZE => 'The size of the uploaded file can be up to %d bytes.',
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
	 * @return Rules      provides a fluent interface
	 */
	public function setRequired($value = TRUE)
	{
		if ($value) {
			$this->addRule(Form::REQUIRED, $value);
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
		return (bool) $this->required;
	}



	/**
	 * Adds a validation rule for the current control.
	 * @param  mixed      rule type
	 * @param  string     message to display for invalid data
	 * @param  mixed      optional rule arguments
	 * @return Rules      provides a fluent interface
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
	 * @return Rules      provides a fluent interface
	 */
	public function toggle($id, $hide = TRUE)
	{
		$this->toggles[$id] = $hide;
		return $this;
	}



	/**
	 * Validates against ruleset.
	 * @return string[]
	 */
	public function validate()
	{
		$errors = array();
		foreach ($this as $rule) {
			if ($rule->control->isDisabled()) {
				continue;
			}

			$success = $this->validateRule($rule);

			if ($rule->type === Rule::CONDITION && $success) {
				if ($errors = $rule->subRules->validate()) {
					break;
				}

			} elseif ($rule->type === Rule::VALIDATOR && !$success) {
				$errors[] = $rule->control->formatMessage($rule, TRUE);
				break;
			}
		}
		return $errors;
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
		return $rule->isNegative xor static::getCallback($rule)->invoke($rule->control, is_array($rule->arg) ? $args : $args[0]);
	}



	/**
	 * Iterates over complete ruleset.
	 * @return \ArrayIterator
	 */
	final public function getIterator()
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

		if (!$this->getCallback($rule)->isCallable()) {
			$operation = is_scalar($rule->operation) ? " '$rule->operation'" : '';
			throw new Nette\InvalidArgumentException("Unknown operation$operation for control '{$rule->control->name}'.");
		}
	}



	private static function getCallback($rule)
	{
		$op = $rule->operation;
		if (is_string($op) && strncmp($op, ':', 1) === 0) {
			return new Nette\Callback(get_class($rule->control), self::VALIDATE_PREFIX . ltrim($op, ':'));
		} else {
			return new Nette\Callback($op);
		}
	}

}
