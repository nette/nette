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
 * Select box control that allows multiple item selection.
 *
 * @author     David Grudl
 */
class MultiSelectBox extends SelectBox
{
	protected $value = array();


	/**
	 * Sets selected items (by keys).
	 * @param  array
	 * @return MultiSelectBox  provides a fluent interface
	 */
	public function setValue($values)
	{
		return $this->setRawValue($values);
	}



	/**
	 * Returns selected keys.
	 * @return array
	 */
	public function getValue()
	{
		return array_values(array_intersect($this->value, array_keys($this->allowed)));
	}



	protected function setRawValue($values)
	{
		$res = array();
		foreach (is_array($values) ? $values : array($values) as $value) {
			if (is_scalar($value)) {
				$res[$value] = NULL;
			}
		}
		$this->value = array_keys($res);
		return $this;
	}



	/**
	 * Returns selected keys (not checked).
	 * @return array
	 */
	public function getRawValue()
	{
		return $this->value;
	}



	/**
	 * Returns selected values.
	 * @return array
	 */
	public function getSelectedItem()
	{
		return array_intersect_key($this->allowed, array_flip($this->value));
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
		return parent::getControl()->multiple(TRUE);
	}



	/**
	 * Count/length validator.
	 * @param  MultiSelectBox
	 * @param  array  min and max length pair
	 * @return bool
	 */
	public static function validateLength(MultiSelectBox $control, $range)
	{
		if (!is_array($range)) {
			$range = array($range, $range);
		}
		$count = count($control->getSelectedItem());
		return ($range[0] === NULL || $count >= $range[0]) && ($range[1] === NULL || $count <= $range[1]);
	}

}
