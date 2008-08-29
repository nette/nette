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



require_once dirname(__FILE__) . '/../../Forms/Controls/TextBase.php';



/**
 * Single line text input control.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Forms
 */
class TextInput extends TextBase
{

	/**
	 * @param  string  control name
	 * @param  string  label
	 * @param  int  width of the control
	 * @param  int  maximum number of characters the user may enter
	 */
	public function __construct($label, $cols = NULL, $maxLenght = NULL)
	{
		parent::__construct($label);
		$this->control->type = 'text';
		$this->control->size = $cols;
		$this->control->maxlength = $maxLenght;
		$this->filters[] = 'trim';
		$this->value = '';
	}



	/**
	 * Loads HTTP data.
	 * @param  array
	 * @return void
	 */
	public function loadHttpData($data)
	{
		parent::loadHttpData($data);

		if ($this->control->type === 'password') {
			$this->rawValue = '';
		}

		if ($this->control->maxlength && iconv_strlen($this->value) > $this->control->maxlength) {
			$this->value = iconv_substr($this->value, 0, $this->control->maxlength);
		}
	}



	/**
	 * Sets or unsets the password mode.
	 * @param  bool
	 * @return TextInput  provides a fluent interface
	 */
	public function setPasswordMode($mode)
	{
		$this->control->type = $mode ? 'password' : 'text';
		return $this;
	}



	/**
	 * Generates control's HTML element.
	 * @return Nette::Web::Html
	 */
	public function getControl()
	{
		$control = parent::getControl();
		$control->value = $this->value === '' ? $this->emptyValue : $this->rawValue;
		return $control;
	}



	public function notifyRule(Rule $rule)
	{
		if (!is_string($rule->operation)) {
			// nothing to do
		} elseif (!$rule->isCondition && strcasecmp($rule->operation, /*Nette::Forms::*/'TextBase::validateLength') === 0) {
			$this->control->maxlength = is_array($rule->arg) ? $rule->arg[1] : $rule->arg;

		} elseif (!$rule->isCondition && strcasecmp($rule->operation, /*Nette::Forms::*/'TextBase::validateMaxLength') === 0) {
			$this->control->maxlength = $rule->arg;
		}

		parent::notifyRule($rule);
	}


}
