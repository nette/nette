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

	/**
	 * Loads HTTP data.
	 * @return void
	 */
	public function loadHttpData()
	{
		$this->value = array_keys(array_flip($this->getHttpData()));
		if (is_array($this->disabled)) {
			$this->value = array_diff($this->value, array_keys($this->disabled));
		}
	}


	/**
	 * Sets selected items (by keys).
	 * @param  array
	 * @return self
	 */
	public function setValue($values)
	{
		if (is_scalar($values) || $values === NULL) {
			$values = (array) $values;
		} elseif (!is_array($values)) {
			throw new Nette\InvalidArgumentException('Value must be array or NULL, ' . gettype($values) . ' given.');
		}
		$flip = array();
		foreach ($values as $value) {
			if (!is_scalar($value) && !method_exists($value, '__toString')) {
				throw new Nette\InvalidArgumentException('Values must be scalar, ' . gettype($value) . ' given.');
			}
			$flip[(string) $value] = TRUE;
		}
		$values = array_keys($flip);
		if ($diff = array_diff($values, array_keys($this->flattenItems))) {
			throw new Nette\InvalidArgumentException("Values '" . implode("', '", $diff) . "' are out of range of current items.");
		}
		$this->value = $values;
		return $this;
	}


	/**
	 * Returns selected keys.
	 * @return array
	 */
	public function getValue()
	{
		return array_values(array_intersect($this->value, array_keys($this->flattenItems)));
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
		return array_intersect_key($this->flattenItems, array_flip($this->value));
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

}
