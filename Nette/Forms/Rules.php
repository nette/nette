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



require_once dirname(__FILE__) . '/../Object.php';



/**
 * List of validation & condition rules.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Forms
 */
final class Rules extends /*Nette::*/Object implements /*::*/IteratorAggregate
{
	const VALIDATE_PREFIX = '::validate';

	/** @var array */
	public static $defaultMessages = array(
	);

	/** @var array of Rule */
	protected $rules = array();

	/** @var array */
	protected $toggles = array();

	/** @var IFormControl */
	protected $control;



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
		return $this->addRuleFor($this->control, $operation, $message, $arg);
	}



	/**
	 * Adds a validation rule for the specified control.
	 * @param  IFormControl form control
	 * @param  mixed      rule type
	 * @param  string     message to display for invalid data
	 * @param  mixed      optional rule arguments
	 * @return Rules      provides a fluent interface
	 */
	public function addRuleFor(IFormControl $control, $operation, $message = NULL, $arg = NULL)
	{
		$rule = new Rule;
		$rule->control = $control;
		$rule->operation = $operation;
		$this->adjustOperation($rule);
		$rule->arg = $arg;
		if ($message === NULL && isset(self::$defaultMessages[$rule->operation])) {
			$rule->message = self::$defaultMessages[$rule->operation];
		} else {
			$rule->message = $message;
		}

		$control->notifyRule($rule);
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
		$rule->isCondition = TRUE;
		$rule->subRules = new self($this->control);

		$control->notifyRule($rule);
		$this->rules[] = $rule;
		return $rule->subRules;
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
	 * @return bool    is valid?
	 */
	public function validate()
	{
		$valid = TRUE;
		foreach ($this->rules as $rule)
		{
			if ($rule->control->getDisabled()) continue;

			try {
				unset($e);
				$ok = ($rule->isNegative xor call_user_func($rule->operation, $rule->control, $rule->arg));
			} catch (ValidateException $e) {
				$ok = FALSE;
			}

			if ($rule->isCondition && $ok) {
				$ok = $rule->subRules->validate();
				$valid = $valid && $ok;

			} elseif (!$rule->isCondition && !$ok) {
				if (isset($e)) {
					$rule->control->addError($e->getMessage());

				} else {
					$message = $rule->message;
					$translator = $rule->control->getTranslator();
					if ($translator !== NULL) {
						$message = $translator->translate($message);
					}
					$rule->control->addError(vsprintf($message, (array) $rule->arg));
				}
				$valid = FALSE;
			}
		}
		return $valid;
	}



	/**
	 * Iterates over ruleset.
	 * @return ::ArrayIterator
	 */
	final public function getIterator()
	{
		return new /*::*/ArrayIterator($this->rules);
	}



	/**
	 * @return array
	 */
	final public function getToggles()
	{
		return $this->toggles;
	}



	/**
	 * Process 'operation' string
	 * @param  Rule
	 * @return void
	 */
	private function adjustOperation($rule)
	{
		if (!is_string($rule->operation)) return;

		if (strncmp($rule->operation, '!', 1) === 0) {
			$rule->isNegative = TRUE;
			$rule->operation = substr($rule->operation, 1);
		}

		if (strncmp($rule->operation, ':', 1) === 0) {
			$rule->operation = get_class($rule->control) . self::VALIDATE_PREFIX . ltrim($rule->operation, ':');
		}
	}

}
