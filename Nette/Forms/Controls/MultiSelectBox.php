<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;


/**
 * Select box control that allows multiple items selection.
 *
 * @author     David Grudl
 */
class MultiSelectBox extends MultiChoiceControl
{
	/** @var array of option / optgroup */
	private $options = array();


	/**
	 * Sets options and option groups from which to choose.
	 * @return self
	 */
	public function setItems(array $items, $useKeys = TRUE)
	{
		if (!$useKeys) {
			$res = array();
			foreach ($items as $key => $value) {
				unset($items[$key]);
				if (is_array($value)) {
					foreach ($value as $val) {
						$res[$key][(string) $val] = $val;
					}
				} else {
					$res[(string) $value] = $value;
				}
			}
			$items = $res;
		}
		$this->options = $items;
		return parent::setItems(Nette\Utils\Arrays::flatten($items, TRUE));
	}


	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$items = array();
		foreach ($this->options as $key => $value) {
			$items[is_array($value) ? $this->translate($key) : $key] = $this->translate($value);
		}

		return Nette\Forms\Helpers::createSelectBox(
			$items,
			array(
				'selected?' => $this->value,
				'disabled:' => is_array($this->disabled) ? $this->disabled : NULL
			)
		)->addAttributes(parent::getControl()->attrs)->multiple(TRUE);
	}


	/** @deprecated */
	function getSelectedItem()
	{
		trigger_error(__METHOD__ . '(TRUE) is deprecated; use getSelectedItems() instead.', E_USER_DEPRECATED);
		return $this->getSelectedItems();
	}

}
