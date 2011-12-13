<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Forms\Controls;

use Nette;



/**
 * Select box control that allows multiple item selection.
 *
 * @author     David Grudl
 */
class MultiSelectBox extends SelectBox
{


	/**
	 * Returns selected keys.
	 * @return array
	 */
	public function getValue()
	{
		$allowed = array_keys($this->allowed);
		if ($this->getPrompt()) {
			unset($allowed[0]);
		}
		return array_intersect($this->getRawValue(), $allowed);
	}



	/**
	 * Returns selected keys (not checked).
	 * @return array
	 */
	public function getRawValue()
	{
		if (is_scalar($this->value)) {
			$value = array($this->value);

		} elseif (!is_array($this->value)) {
			$value = array();

		} else {
			$value = $this->value;
		}

		$res = array();
		foreach ($value as $val) {
			if (is_scalar($val)) {
				$res[] = $val;
			}
		}
		return $res;
	}



	/**
	 * Returns selected values.
	 * @return array
	 */
	public function getSelectedItem()
	{
		if (!$this->areKeysUsed()) {
			return $this->getValue();

		} else {
			$res = array();
			foreach ($this->getValue() as $value) {
				$res[$value] = $this->allowed[$value];
			}
			return $res;
		}
	}



	/**
	 * Returns HTML name of control.
	 * @return string
	 */
	public function getHtmlName()
	{
		return parent::getHtmlName() . '[]';
	}



	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$control = parent::getControl();
		$control->multiple = TRUE;
		return $control;
	}



	/**
	 * Min-length validator: has control's value minimal length?
	 * @param  MultiSelectBox
	 * @param  int  length
	 * @return bool
	 */
	public static function validateMinLength(MultiSelectBox $control, $length)
	{
		return count($control->getSelectedItem()) >= $length;
	}



	/**
	 * Max-length validator: is control's value length in limit?
	 * @param  MultiSelectBox
	 * @param  int  length
	 * @return bool
	 */
	public static function validateMaxLength(MultiSelectBox $control, $length)
	{
		return count($control->getSelectedItem()) <= $length;
	}



	/**
	 * Length validator: is control's value length in range?
	 * @param  MultiSelectBox
	 * @param  array  min and max length pair
	 * @return bool
	 */
	public static function validateLength(MultiSelectBox $control, $range)
	{
		if (!is_array($range)) {
			$range = array($range, $range);
		}
		$len = count($control->getSelectedItem());
		return ($range[0] === NULL || $len >= $range[0]) && ($range[1] === NULL || $len <= $range[1]);
	}

}
