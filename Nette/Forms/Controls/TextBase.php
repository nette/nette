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
	 * Sets control's value.
	 * @param  string
	 * @return TextBase  provides a fluent interface
	 */
	public function setValue($value)
	{
		if (!is_scalar($value) && $value !== NULL) {
			throw new Nette\InvalidArgumentException('Value must be scalar or NULL, ' . gettype($value) . ' given.');
		}
		$this->value = (string) $value;
		return $this;
	}


	/**
	 * Returns control's value.
	 * @return string
	 */
	public function getValue()
	{
		$value = (string) $this->value;
		if (!empty($this->control->maxlength)) {
			$value = Nette\Utils\Strings::substring($value, 0, $this->control->maxlength);
		}
		foreach ($this->filters as $filter) {
			$value = (string) $filter($value);
		}
		return $value === $this->translate($this->emptyValue) ? '' : $value;
	}


	/**
	 * Sets the special value which is treated as empty string.
	 * @param  string
	 * @return TextBase  provides a fluent interface
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
	final public function getEmptyValue()
	{
		return $this->emptyValue;
	}


	/**
	 * Appends input string filter callback.
	 * @param  callable
	 * @return TextBase  provides a fluent interface
	 */
	public function addFilter($filter)
	{
		$this->filters[] = new Nette\Callback($filter);
		return $this;
	}


	public function getControl()
	{
		$el = parent::getControl();
		if ($this->emptyValue !== '') {
			$el->data('nette-empty-value', $this->translate($this->emptyValue));
		}
		if (isset($el->placeholder)) {
			$el->placeholder = $this->translate($el->placeholder);
		}
		return $el;
	}


	public function addRule($operation, $message = NULL, $arg = NULL)
	{
		if ($operation === Form::FLOAT) {
			$this->addFilter(array(__CLASS__, 'filterFloat'));

		} elseif ($operation === Form::URL) {
			$this->addFilter(array(__CLASS__, 'filterUrl'));

		} elseif ($operation === Form::LENGTH || $operation === Form::MAX_LENGTH) {
			$tmp = is_array($arg) ? $arg[1] : $arg;
			$this->control->maxlength = is_scalar($tmp) ? $tmp : NULL;
		}
		return parent::addRule($operation, $message, $arg);
	}


	/********************* validators ****************d*g**/


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


	/**
	 * URL string cleanup.
	 * @param  string
	 * @return string
	 */
	public static function filterUrl($s)
	{
		return Validators::isUrl('http://' . $s) ? 'http://' . $s : $s;
	}


	/** @deprecated */
	public static function validateRegexp(TextBase $control, $regexp)
	{
		trigger_error('Validator REGEXP is deprecated; use PATTERN instead (which is matched against the entire value and is case sensitive).', E_USER_DEPRECATED);
		return (bool) Strings::match($control->getValue(), $regexp);
	}


	/**
	 * Regular expression validator: matches control's value regular expression?
	 * @param  TextBase
	 * @param  string
	 * @return bool
	 */
	public static function validatePattern(TextBase $control, $pattern)
	{
		return (bool) Strings::match($control->getValue(), "\x01^($pattern)\\z\x01u");
	}


	/**
	 * Integer validator: is a control's value decimal number?
	 * @param  TextBase
	 * @return bool
	 */
	public static function validateInteger(TextBase $control)
	{
		return Validators::isNumericInt($control->getValue());
	}


	/**
	 * Float validator: is a control's value float number?
	 * @param  TextBase
	 * @return bool
	 */
	public static function validateFloat(TextBase $control)
	{
		return Validators::isNumeric(static::filterFloat($control->getValue()));
	}


	/**
	 * Float string cleanup.
	 * @param  string
	 * @return string
	 */
	public static function filterFloat($s)
	{
		return str_replace(array(' ', ','), array('', '.'), $s);
	}

}
