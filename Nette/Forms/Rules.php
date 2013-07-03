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
	/** @deprecated */
	public static $defaultMessages;

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
		return (bool) $this->required;
	}


	/**
	 * Adds a validation rule for the current control.
	 * @param  mixed      rule type
	 * @param  string     message to display for invalid data
	 * @param  mixed      optional rule arguments
	 * @return self
	 */
	public function addRule($validator, $message = NULL, $arg = NULL)
	{
		$rule = new Rule;
		$rule->control = $this->control;
		$rule->validator = $validator;
		$this->adjustOperation($rule);
		$rule->arg = $arg;
		$rule->message = $message;
		if ($rule->validator === Form::REQUIRED) {
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
	public function addCondition($validator, $arg = NULL)
	{
		return $this->addConditionOn($this->control, $validator, $arg);
	}


	/**
	 * Adds a validation condition on specified control a returns new branch.
	 * @param  IControl form control
	 * @param  mixed      condition type
	 * @param  mixed      optional condition arguments
	 * @return Rules      new branch
	 */
	public function addConditionOn(IControl $control, $validator, $arg = NULL)
	{
		$rule = new Rule;
		$rule->control = $control;
		$rule->validator = $validator;
		$this->adjustOperation($rule);
		$rule->arg = $arg;
		$rule->branch = new static($this->control);
		$rule->branch->parent = $this;

		$this->rules[] = $rule;
		return $rule->branch;
	}


	/**
	 * Adds a else statement.
	 * @return Rules      else branch
	 */
	public function elseCondition()
	{
		$rule = clone end($this->parent->rules);
		$rule->isNegative = !$rule->isNegative;
		$rule->branch = new static($this->parent->control);
		$rule->branch->parent = $this->parent;
		$this->parent->rules[] = $rule;
		return $rule->branch;
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

			if ($success && $rule->branch && !$rule->branch->validate()) {
				return FALSE;

			} elseif (!$success && !$rule->branch) {
				$rule->control->addError(Validator::formatMessage($rule, TRUE));
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
			if ($rule->branch) {
				$success = static::validateRule($rule);
				foreach ($rule->branch->getToggles(TRUE) as $id => $hide) {
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
		if (is_string($rule->validator) && ord($rule->validator[0]) > 127) {
			$rule->isNegative = TRUE;
			$rule->validator = ~$rule->validator;
		}

		if (!is_callable($this->getCallback($rule))) {
			$validator = is_scalar($rule->validator) ? " '$rule->validator'" : '';
			throw new Nette\InvalidArgumentException("Unknown validator$validator for control '{$rule->control->name}'.");
		}
	}


	private static function getCallback($rule)
	{
		$op = $rule->validator;
		if (is_string($op) && strncmp($op, ':', 1) === 0) {
			return 'Nette\Forms\Validator::validate' . ltrim($op, ':');
		} else {
			return $op;
		}
	}

}

Rules::$defaultMessages = & Validator::$messages;
