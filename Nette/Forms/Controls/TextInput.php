<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
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
	 * Loads HTTP data.
	 * @return void
	 */
	public function loadHttpData()
	{
		$this->setValue($this->getHttpData(Nette\Forms\Form::DATA_LINE));
	}


	/**
	 * Changes control's type attribute.
	 * @param  string
	 * @return self
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
		$input = parent::getControl();

		foreach ($this->getRules() as $rule) {
			if ($rule->isNegative || $rule->type !== Nette\Forms\Rule::VALIDATOR) {

			} elseif ($rule->operation === Nette\Forms\Form::RANGE
				&& in_array($input->type, array('number', 'range', 'datetime-local', 'datetime', 'date', 'month', 'week', 'time'))
			) {
				if (isset($rule->arg[0]) && is_scalar($rule->arg[0])) {
					$input->min = isset($input->min) ? max($input->min, $rule->arg[0]) : $rule->arg[0];
				}
				if (isset($rule->arg[1]) && is_scalar($rule->arg[1])) {
					$input->max = isset($input->max) ? min($input->max, $rule->arg[1]) : $rule->arg[1];
				}

			} elseif ($rule->operation === Nette\Forms\Form::PATTERN && is_scalar($rule->arg)
				&& in_array($input->type, array('text', 'search', 'tel', 'url', 'email', 'password'))
			) {
				$input->pattern = $rule->arg;
			}
		}

		if ($input->type !== 'password') {
			$input->value = $this->rawValue === '' ? $this->translate($this->emptyValue) : $this->rawValue;
		}
		return $input;
	}

}
