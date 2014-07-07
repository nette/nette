<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette,
	Nette\Forms\Form,
	Nette\Utils\Strings,
	Nette\Utils\Validators;


/**
 * Implements the basic functionality common to text input controls.
 *
 * @author     David Grudl
 *
 * @property   string $emptyValue
 */
abstract class TextBase extends BaseControl
{
	/** @var string */
	protected $emptyValue = '';

	/** @var array */
	protected $filters = array();


	/**
	 * @param  string  label
	 */
	public function __construct($label = NULL)
	{
		parent::__construct($label);
		$this->addFilter($this->sanitize);
	}


	/**
	 * Sets control's value.
	 * @param  string
	 * @return self
	 */
	public function setValue($value)
	{
		$this->value = is_array($value) ? '' : str_replace("\r\n", "\n", $value);
		return $this;
	}


	/**
	 * Returns control's value.
	 * @return string
	 */
	public function getValue()
	{
		$value = $this->value;
		foreach ($this->filters as $filter) {
			$value = (string) $filter/*5.2*->invoke*/($value);
		}
		return $value === $this->translate($this->emptyValue) ? '' : $value;
	}


	/**
	 * Sets the special value which is treated as empty string.
	 * @param  string
	 * @return self
	 */
	public function setEmptyValue($value)
	{
		$this->emptyValue = (string) $value;
		return $this;
	}


	/**
	 * Returns the special value which is treated as empty string.
	 * @return string
	 */
	public function getEmptyValue()
	{
		return $this->emptyValue;
	}


	/**
	 * Appends input string filter callback.
	 * @param  callable
	 * @return self
	 */
	public function addFilter($filter)
	{
		$this->filters[] = new Nette\Callback($filter);
		return $this;
	}


	/**
	 * Filter: removes unnecessary whitespace and shortens value to control's max length.
	 * @return string
	 */
	public function sanitize($value)
	{
		if ($this->control->maxlength) {
			$value = Nette\Utils\Strings::substring($value, 0, $this->control->maxlength);
		}
		if (strcasecmp($this->control->getName(), 'input') === 0) {
			$value = Nette\Utils\Strings::trim(strtr($value, "\r\n", '  '));
		}
		return $value;
	}


	public function getControl()
	{
		$control = parent::getControl();
		foreach ($this->getRules() as $rule) {
			if ($rule->type === Nette\Forms\Rule::VALIDATOR && !$rule->isNegative
				&& ($rule->operation === Form::LENGTH || $rule->operation === Form::MAX_LENGTH)
			) {
				$control->maxlength = is_array($rule->arg) ? $rule->arg[1] : $rule->arg;
			}
		}
		if ($this->emptyValue !== '') {
			$control->data('nette-empty-value', $this->translate($this->emptyValue));
		}
		return $control;
	}


	public function addRule($operation, $message = NULL, $arg = NULL)
	{
		if ($operation === Form::FLOAT) {
			$this->addFilter(array(__CLASS__, 'filterFloat'));

		} elseif ($operation === Form::LENGTH || $operation === Form::MAX_LENGTH) {
			$tmp = is_array($arg) ? $arg[1] : $arg;
			$this->control->maxlength = is_scalar($tmp) ? $tmp : NULL;
		}
		return parent::addRule($operation, $message, $arg);
	}


	/********************* validators ****************d*g**/


	/**
	 * Min-length validator: has control's value minimal length?
	 * @param  TextBase
	 * @param  int  length
	 * @return bool
	 * @internal
	 */
	public static function validateMinLength(TextBase $control, $length)
	{
		return Strings::length($control->getValue()) >= $length;
	}


	/**
	 * Max-length validator: is control's value length in limit?
	 * @param  TextBase
	 * @param  int  length
	 * @return bool
	 * @internal
	 */
	public static function validateMaxLength(TextBase $control, $length)
	{
		return Strings::length($control->getValue()) <= $length;
	}


	/**
	 * Length validator: is control's value length in range?
	 * @param  TextBase
	 * @param  array  min and max length pair
	 * @return bool
	 */
	public static function validateLength(TextBase $control, $range)
	{
		if (!is_array($range)) {
			$range = array($range, $range);
		}
		return Validators::isInRange(Strings::length($control->getValue()), $range);
	}


	/**
	 * Email validator: is control's value valid email address?
	 * @param  TextBase
	 * @return bool
	 */
	public static function validateEmail(TextBase $control)
	{
		return Validators::isEmail($control->getValue());
	}


	/**
	 * URL validator: is control's value valid URL?
	 * @param  TextBase
	 * @return bool
	 */
	public static function validateUrl(TextBase $control)
	{
		return Validators::isUrl($control->getValue()) || Validators::isUrl('http://' . $control->getValue());
	}


	/** @deprecated */
	public static function validateRegexp(TextBase $control, $regexp)
	{
		return (bool) Strings::match($control->getValue(), $regexp);
	}


	/**
	 * Matches control's value regular expression?
	 * @return bool
	 * @internal
	 */
	public static function validatePattern(TextBase $control, $pattern)
	{
		return (bool) Strings::match($control->getValue(), "\x01^($pattern)\\z\x01u");
	}


	/**
	 * Is a control's value decimal number?
	 * @return bool
	 * @internal
	 */
	public static function validateInteger(TextBase $control)
	{
		return Validators::isNumericInt($control->getValue());
	}


	/**
	 * Is a control's value float number?
	 * @return bool
	 * @internal
	 */
	public static function validateFloat(TextBase $control)
	{
		return Validators::isNumeric(static::filterFloat($control->getValue()));
	}


	/**
	 * Rangle validator: is a control's value number in specified range?
	 * @param  TextBase
	 * @param  array  min and max value pair
	 * @return bool
	 */
	public static function validateRange(TextBase $control, $range)
	{
		return Validators::isInRange($control->getValue(), $range);
	}


	/**
	 * Float string cleanup.
	 * @param  string
	 * @return string
	 * @internal
	 */
	public static function filterFloat($s)
	{
		return str_replace(array(' ', ','), array('', '.'), $s);
	}

}
