<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;


/**
 * Choice control that allows multiple items selection.
 *
 * @author     David Grudl
 *
 * @property   array $items
 * @property-read array $selectedItems
 * @property-read array $rawValue
 */
abstract class MultiChoiceControl extends BaseControl
{
	/** @var array */
	private $items = array();


	public function __construct($label = NULL, array $items = NULL)
	{
		parent::__construct($label);
		if ($items !== NULL) {
			$this->setItems($items);
		}
	}


	/**
	 * Loads HTTP data.
	 * @return void
	 */
	public function loadHttpData()
	{
		$this->value = array_keys(array_flip($this->getHttpData(Nette\Forms\Form::DATA_TEXT)));
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
			throw new Nette\InvalidArgumentException(sprintf("Value must be array or NULL, %s given in field '%s'.", gettype($values), $this->name));
		}
		$flip = array();
		foreach ($values as $value) {
			if (!is_scalar($value) && !method_exists($value, '__toString')) {
				throw new Nette\InvalidArgumentException(sprintf("Values must be scalar, %s given in field '%s'.", gettype($value), $this->name));
			}
			$flip[(string) $value] = TRUE;
		}
		$values = array_keys($flip);
		if ($diff = array_diff($values, array_keys($this->items))) {
			$range = Nette\Utils\Strings::truncate(implode(', ', array_map(function($s) { return var_export($s, TRUE); }, array_keys($this->items))), 70, '...');
			$vals = (count($diff) > 1 ? 's' : '') . " '" . implode("', '", $diff) . "'";
			throw new Nette\InvalidArgumentException("Value$vals are out of allowed range [$range] in field '{$this->name}'.");
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
		return array_values(array_intersect($this->value, array_keys($this->items)));
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
	 * Is any item selected?
	 * @return bool
	 */
	public function isFilled()
	{
		return $this->getValue() !== array();
	}


	/**
	 * Sets items from which to choose.
	 * @param  array
	 * @param  bool
	 * @return self
	 */
	public function setItems(array $items, $useKeys = TRUE)
	{
		$this->items = $useKeys ? $items : array_combine($items, $items);
		return $this;
	}


	/**
	 * Returns items from which to choose.
	 * @return array
	 */
	public function getItems()
	{
		return $this->items;
	}


	/**
	 * Returns selected values.
	 * @return array
	 */
	public function getSelectedItems()
	{
		return array_intersect_key($this->items, array_flip($this->value));
	}


	/**
	 * Disables or enables control or items.
	 * @param  bool|array
	 * @return self
	 */
	public function setDisabled($value = TRUE)
	{
		if (!is_array($value)) {
			return parent::setDisabled($value);
		}

		parent::setDisabled(FALSE);
		$this->disabled = array_fill_keys($value, TRUE);
		$this->value = array_diff($this->value, $value);
		return $this;
	}


	/**
	 * Returns HTML name of control.
	 * @return string
	 */
	public function getHtmlName()
	{
		return parent::getHtmlName() . '[]';
	}

}
