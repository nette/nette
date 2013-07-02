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

use Nette;


/**
 * Single line text input control.
 *
 * @author     David Grudl
 * @property-write $type
 */
class TextInput extends TextBase
{

	/**
	 * @param  string  label
	 * @param  int  maximum number of characters the user may enter
	 */
	public function __construct($label = NULL, $maxLength = NULL)
	{
		parent::__construct($label);
		$this->control->type = 'text';
		$this->control->maxlength = $maxLength;
	}


	/**
	 * Changes control's type attribute.
	 * @param  string
	 * @return BaseControl  provides a fluent interface
	 */
	public function setType($type)
	{
		$this->control->type = $type;
		return $this;
	}


	/** @deprecated */
	public function setPasswordMode($mode = TRUE)
	{
		trigger_error(__METHOD__ . '() is deprecated; use setType("password") instead.', E_USER_DEPRECATED);
		$this->control->type = $mode ? 'password' : 'text';
		return $this;
	}


	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$control = parent::getControl();
		foreach ($this->getRules() as $rule) {
			if ($rule->isNegative || $rule->type !== Nette\Forms\Rule::VALIDATOR) {

			} elseif ($rule->operation === Nette\Forms\Form::RANGE && $control->type !== 'text') {
				$control->min = isset($rule->arg[0]) && is_scalar($rule->arg[0]) ? $rule->arg[0] : NULL;
				$control->max = isset($rule->arg[1]) && is_scalar($rule->arg[1]) ? $rule->arg[1] : NULL;

			} elseif ($rule->operation === Nette\Forms\Form::PATTERN && is_scalar($rule->arg)) {
				$control->pattern = $rule->arg;
			}
		}
		if ($control->type !== 'password') {
			$control->value = $this->getValue() === '' ? $this->translate($this->emptyValue) : $this->value;
		}
		return $control;
	}

}
