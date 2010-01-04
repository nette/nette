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
 * Single line text input control.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Forms
 */
class TextInput extends TextBase
{

	/**
	 * @param  string  control name
	 * @param  string  label
	 * @param  int  width of the control
	 * @param  int  maximum number of characters the user may enter
	 */
	public function __construct($label = NULL, $cols = NULL, $maxLength = NULL)
	{
		parent::__construct($label);
		$this->control->type = 'text';
		$this->control->size = $cols;
		$this->control->maxlength = $maxLength;
		$this->filters[] = array(/*Nette\*/'String', 'trim');
		$this->filters[] = array($this, 'checkMaxLength');
		$this->value = '';
	}



	/**
	 * Filter: shortens value to control's max length.
	 * @return string
	 */
	protected function checkMaxLength($value)
	{
		if ($this->control->maxlength && iconv_strlen($value, 'UTF-8') > $this->control->maxlength) {
			$value = iconv_substr($value, 0, $this->control->maxlength, 'UTF-8');
		}
		return $value;
	}



	/**
	 * Sets or unsets the password mode.
	 * @param  bool
	 * @return TextInput  provides a fluent interface
	 */
	public function setPasswordMode($mode = TRUE)
	{
		$this->control->type = $mode ? 'password' : 'text';
		return $this;
	}



	/**
	 * Generates control's HTML element.
	 * @return Nette\Web\Html
	 */
	public function getControl()
	{
		$control = parent::getControl();
		if ($this->control->type !== 'password') {
			$control->value = $this->getValue() === '' ? $this->translate($this->emptyValue) : $this->value;
		}
		return $control;
	}



	public function notifyRule(Rule $rule)
	{
		if (is_string($rule->operation) && strcasecmp($rule->operation, ':length') === 0 && !$rule->isNegative) {
			$this->control->maxlength = is_array($rule->arg) ? $rule->arg[1] : $rule->arg;

		} elseif (is_string($rule->operation) && strcasecmp($rule->operation, ':maxLength') === 0 && !$rule->isNegative) {
			$this->control->maxlength = $rule->arg;
		}

		parent::notifyRule($rule);
	}


}
