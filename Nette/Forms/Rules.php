<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Forms
 */

/*namespace Nette\Forms;*/



/**
 * List of validation & condition rules.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Forms
 */
final class Rules extends /*Nette\*/Object implements /*\*/IteratorAggregate
{
	/** @internal */
	const VALIDATE_PREFIX = 'validate';

	/** @var array */
	public static $defaultMessages = array(
	);

	/** @var array of Rule */
	private $rules = array();

	/** @var Rules */
	private $parent;

	/** @var array */
	private $toggles = array();

	/** @var IFormControl */
	private $control;



	public function __construct(IFormControl $control)
	{
		$this->control = $control;
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
		if ($message === NULL && isset(self::$defaultMessages[$rule->operation])) {
			$rule->message = self::$defaultMessages[$rule->operation];
		} else {
			$rule->message = $message;
		}

		if ($this->parent === NULL) {
			// notify only direct rules
			$this->control->notifyRule($rule);
		}
		$this->rules[] = $rule;
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
	 * @param  IFormControl form control
	 * @param  mixed      condition type
	 * @param  mixed      optional condition arguments
	 * @return Rules      new branch
	 */
	public function addConditionOn(IFormControl $control, $operation, $arg = NULL)
	{
		$rule = new Rule;
		$rule->control = $control;
		$rule->operation = $operation;
		$this->adjustOperation($rule);
		$rule->arg = $arg;
		$rule->type = Rule::CONDITION;
		$rule->subRules = new self($this->control);
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
		$rule->subRules = new self($this->parent->control);
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
	 * @param  bool    stop before first error?
	 * @return bool    is valid?
	 */
	public function validate($onlyCheck = FALSE)
	{
		$valid = TRUE;
		foreach ($this->rules as $rule)
		{
			if ($rule->control->isDisabled()) continue;

			$success = ($rule->isNegative xor call_user_func($this->getCallback($rule), $rule->control, $rule->arg));

			if ($rule->type === Rule::CONDITION && $success) {
				$success = $rule->subRules->validate($onlyCheck);
				$valid = $valid && $success;

			} elseif ($rule->type === Rule::VALIDATOR && !$success) {
				if ($onlyCheck) {
					return FALSE;
				}
				$rule->control->addError(vsprintf($rule->control->translate($rule->message, is_int($rule->arg) ? $rule->arg : NULL), (array) $rule->arg));
				$valid = FALSE;
				if ($rule->breakOnFailure) {
					break;
				}
			}
		}
		return $valid;
	}



	/**
	 * Iterates over ruleset.
	 * @return \ArrayIterator
	 */
	final public function getIterator()
	{
		return new /*\*/ArrayIterator($this->rules);
	}



	/**
	 * @return array
	 */
	final public function getToggles()
	{
		return $this->toggles;
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

		// check callback
		if (!is_callable($this->getCallback($rule))) {
			$operation = is_scalar($rule->operation) ? " '$rule->operation'" : '';
			throw new /*\*/InvalidArgumentException("Unknown operation$operation for control '{$rule->control->name}'.");
		}
	}



	private function getCallback($rule)
	{
		$op = $rule->operation;
		if (is_string($op) && strncmp($op, ':', 1) === 0) {
			return array(get_class($rule->control), self::VALIDATE_PREFIX . ltrim($op, ':'));

		} else {
			/**/fixCallback($op);/**/
			return $op;
		}
	}

}
