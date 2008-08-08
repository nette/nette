<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com/
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com/
 * @category   Nette
 * @package    Nette::Forms
 */

/*namespace Nette::Forms;*/



require_once dirname(__FILE__) . '/../../Forms/Controls/FormControl.php';



/**
 * Implements the basic functionality common to text input controls.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Forms
 * @version    $Revision$ $Date$
 */
abstract class TextBase extends FormControl
{
	/** @var string */
	protected $emptyValue = '';

	/** @var string original submitted value */
	protected $rawValue;

	/** @var array */
	protected $filters = array();



	/**
	 * Sets control's value.
	 * @param  string
	 * @return void
	 */
	public function setValue($value)
	{
		$value = (string) $value;
		foreach ($this->filters as $filter) {
			$value = (string) call_user_func($filter, $value);
		}
		$this->rawValue = $this->value = $value === $this->emptyValue ? '' : $value;
	}



	/**
	 * Loads HTTP data.
	 * @param  array
	 * @return void
	 */
	public function loadHttpData($data)
	{
		$name = $this->getName();
		$rawValue = isset($data[$name]) ? $data[$name] : NULL;
		$this->setValue($rawValue);
		$this->rawValue = $rawValue;
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
	 * Appends string filter callback.
	 * @param  callback
	 * @return TextBase  provides a fluent interface
	 */
	public function addFilter($filter)
	{
		$this->filters[] = $filter;
		return $this;
	}



	public function notifyRule(Rule $rule)
	{
		if (is_string($rule->operation) && strcasecmp($rule->operation, /*Nette::Forms::*/'TextBase::validateRegexp') === 0 && $rule->arg[0] !== '/') {
			throw new /*::*/InvalidArgumentException('Regular expression must be JavaScript compatible.');
		}
		parent::notifyRule($rule);
	}



	/**
	 * Min-length validator: has control's value minimal length?
	 * @param  IFormControl
	 * @param  int  length
	 * @return bool
	 */
	public static function validateMinLength(IFormControl $control, $length)
	{
		// bug #33268 iconv_strlen works since PHP 5.0.5
		return iconv_strlen($control->getValue()) >= $length;
	}



	/**
	 * Max-length validator: is control's value length in limit?
	 * @param  IFormControl
	 * @param  int  length
	 * @return bool
	 */
	public static function validateMaxLength(IFormControl $control, $length)
	{
		return iconv_strlen($control->getValue()) <= $length;
	}



	/**
	 * Length validator: is control's value length in range?
	 * @param  IFormControl
	 * @param  array  min and max length pair
	 * @return bool
	 */
	public static function validateLength(IFormControl $control, $range)
	{
		return iconv_strlen($control->getValue()) >= $range[0] && iconv_strlen($control->getValue()) <= $range[1];
	}



	/**
	 * Email validator: is control's value valid email address?
	 * @param  IFormControl
	 * @return bool
	 */
	public static function validateEmail(IFormControl $control)
	{
		return preg_match('/^[^@]+@[^@]+\.[a-z]{2,6}$/i', $control->getValue());
	}



	/**
	 * URL validator: is control's value valid URL?
	 * @param  IFormControl
	 * @return bool
	 */
	public static function validateUrl(IFormControl $control)
	{
		return preg_match('/^.+\.[a-z]{2,6}(\\/.*)?$/i', $control->getValue());
	}



	/**
	 * Regular expression validator: matches control's value regular expression?
	 * @param  IFormControl
	 * @param  string
	 * @return bool
	 */
	public static function validateRegexp(IFormControl $control, $regexp)
	{
		return preg_match($regexp, $control->getValue());
	}



	/**
	 * Numeric validator: is a control's value decimal number?
	 * @param  IFormControl
	 * @return bool
	 */
	public static function validateNumeric(IFormControl $control)
	{
		return preg_match('/^-?[0-9]+$/', $control->getValue());
	}



	/**
	 * Float validator: is a control's value float number?
	 * @param  IFormControl
	 * @return bool
	 */
	public static function validateFloat(IFormControl $control)
	{
		return preg_match('/^-?[0-9]*[.,]?[0-9]+$/', $control->getValue());
	}



	/**
	 * Rangle validator: is a control's value number in specified range?
	 * @param  IFormControl
	 * @param  array  min and max value pair
	 * @return bool
	 */
	public static function validateRange(IFormControl $control, $range)
	{
		return $control->getValue() >= $range[0] && $control->getValue() <= $range[1];
	}

}
