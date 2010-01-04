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
 * Implements the basic functionality common to text input controls.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Forms
 *
 * @property   string $emptyValue
 */
abstract class TextBase extends FormControl
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
		$this->value = is_scalar($value) ? (string) $value : '';
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
			$value = (string) call_user_func($filter, $value);
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
		$this->emptyValue = $value;
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
	 * @param  callback
	 * @return TextBase  provides a fluent interface
	 */
	public function addFilter($filter)
	{
		/**/fixCallback($filter);/**/
		if (!is_callable($filter)) {
			$able = is_callable($filter, TRUE, $textual);
			throw new /*\*/InvalidArgumentException("Filter '$textual' is not " . ($able ? 'callable.' : 'valid PHP callback.'));
		}
		$this->filters[] = $filter;
		return $this;
	}



	public function notifyRule(Rule $rule)
	{
		if (is_string($rule->operation) && strcasecmp($rule->operation, ':float') === 0) {
			$this->addFilter(array(__CLASS__, 'filterFloat'));
		}

		parent::notifyRule($rule);
	}



	/**
	 * Min-length validator: has control's value minimal length?
	 * @param  TextBase
	 * @param  int  length
	 * @return bool
	 */
	public static function validateMinLength(TextBase $control, $length)
	{
		return iconv_strlen($control->getValue(), 'UTF-8') >= $length;
	}



	/**
	 * Max-length validator: is control's value length in limit?
	 * @param  TextBase
	 * @param  int  length
	 * @return bool
	 */
	public static function validateMaxLength(TextBase $control, $length)
	{
		return iconv_strlen($control->getValue(), 'UTF-8') <= $length;
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
		$len = iconv_strlen($control->getValue(), 'UTF-8');
		return ($range[0] === NULL || $len >= $range[0]) && ($range[1] === NULL || $len <= $range[1]);
	}



	/**
	 * Email validator: is control's value valid email address?
	 * @param  TextBase
	 * @return bool
	 */
	public static function validateEmail(TextBase $control)
	{
		$atom = "[-a-z0-9!#$%&'*+/=?^_`{|}~]"; // RFC 5322 unquoted characters in local-part
		$localPart = "(\"([ !\\x23-\\x5B\\x5D-\\x7E]*|\\\\[ -~])+\"|$atom+(\\.$atom+)*)"; // quoted or unquoted
		$chars = "a-z0-9\x80-\xFF"; // superset of IDN
		$domain = "[$chars]([-$chars]{0,61}[$chars])"; // RFC 1034 one domain component
		return (bool) preg_match("(^$localPart@($domain?\\.)+[a-z]{2,14}\\z)i", $control->getValue()); // strict top-level domain
	}



	/**
	 * URL validator: is control's value valid URL?
	 * @param  TextBase
	 * @return bool
	 */
	public static function validateUrl(TextBase $control)
	{
		return (bool) preg_match('/^.+\.[a-z]{2,6}(\\/.*)?$/i', $control->getValue());
	}



	/**
	 * Regular expression validator: matches control's value regular expression?
	 * @param  TextBase
	 * @param  string
	 * @return bool
	 */
	public static function validateRegexp(TextBase $control, $regexp)
	{
		return (bool) preg_match($regexp, $control->getValue());
	}



	/**
	 * Integer validator: is a control's value decimal number?
	 * @param  TextBase
	 * @return bool
	 */
	public static function validateInteger(TextBase $control)
	{
		return (bool) preg_match('/^-?[0-9]+$/', $control->getValue());
	}



	/**
	 * Float validator: is a control's value float number?
	 * @param  TextBase
	 * @return bool
	 */
	public static function validateFloat(TextBase $control)
	{
		return (bool) preg_match('/^-?[0-9]*[.,]?[0-9]+$/', $control->getValue());
	}



	/**
	 * Rangle validator: is a control's value number in specified range?
	 * @param  TextBase
	 * @param  array  min and max value pair
	 * @return bool
	 */
	public static function validateRange(TextBase $control, $range)
	{
		return ($range[0] === NULL || $control->getValue() >= $range[0]) && ($range[1] === NULL || $control->getValue() <= $range[1]);
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
