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
	 * Loads HTTP data.
	 * @return void
	 */
	public function loadHttpData()
	{
		$this->value = array_keys(array_flip($this->getHttpData()));
	}


	/**
	 * Sets selected items (by keys).
	 * @param  array
	 * @return MultiSelectBox  provides a fluent interface
	 */
	public function setValue($values)
	{
		if (is_scalar($values) || $values === NULL) {
			$values = (array) $values;
		} elseif (!is_array($values)) {
			throw new Nette\InvalidArgumentException('Value must be array or NULL, ' . gettype($values) . ' given.');
		}
		if ($diff = array_diff($values, array_keys($this->allowed))) {
			throw new Nette\InvalidArgumentException("Values '" . implode("', '", $diff) . "' are out of range of current items.");
		}
		$this->value = array_keys(array_flip($values));
		return $this;
	}


	/**
	 * Returns selected keys.
	 * @return array
	 */
	public function getValue()
	{
		return array_values(array_intersect($this->value, array_keys($this->allowed)));
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

}
