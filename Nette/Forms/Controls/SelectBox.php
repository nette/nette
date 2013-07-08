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
 * Select box control that allows single item selection.
 *
 * @author     David Grudl
 *
 * @property-read mixed $rawValue
 * @property   bool $prompt
 * @property   array $items
 * @property-read string $selectedItem
 */
class SelectBox extends BaseControl
{
	/** validation rule */
	const VALID = ':selectBoxValid';

	/** @var array */
	private $items = array();

	/** @var array */
	protected $flattenItems = array();

	/** @var mixed */
	private $prompt = FALSE;


	/**
	 * @param  string  label
	 * @param  array   items from which to choose
	 * @param  int     number of rows that should be visible
	 */
	public function __construct($label = NULL, array $items = NULL, $size = NULL)
	{
		parent::__construct($label);
		$this->control->size = $size > 1 ? (int) $size : NULL;
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
		$this->value = $this->getHttpData();
		if ($this->value !== NULL) {
			if (is_array($this->disabled) && isset($this->disabled[$this->value])) {
				$this->value = NULL;
			} else {
				$this->value = key(array($this->value => NULL));
			}
		}
	}


	/**
	 * Sets selected items (by keys).
	 * @param  string
	 * @return self
	 */
	public function setValue($value)
	{
		if ($value !== NULL && !isset($this->flattenItems[(string) $value])) {
			throw new Nette\InvalidArgumentException("Value '$value' is out of range of current items.");
		}
		$this->value = $value === NULL ? NULL : key(array((string) $value => NULL));
		return $this;
	}


	/**
	 * Returns selected item key.
	 * @return mixed
	 */
	public function getValue()
	{
		return isset($this->flattenItems[$this->value]) ? $this->value : NULL;
	}


	/**
	 * Returns selected item key (not checked).
	 * @return mixed
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
		$value = $this->getValue();
		return $value !== NULL && $value !== array();
	}


	/**
	 * Sets first prompt item in select box.
	 * @param  string
	 * @return self
	 */
	public function setPrompt($prompt)
	{
		if ($prompt === TRUE) { // back compatibility
			trigger_error(__METHOD__ . '(TRUE) is deprecated; argument must be string.', E_USER_DEPRECATED);
			$prompt = reset($this->items);
			unset($this->flattenItems[key($this->items)], $this->items[key($this->items)]);
		}
		$this->prompt = $prompt;
		return $this;
	}


	/**
	 * Returns first prompt item?
	 * @return mixed
	 */
	final public function getPrompt()
	{
		return $this->prompt;
	}


	/**
	 * Sets items from which to choose.
	 * @param  array
	 * @param  bool
	 * @return self
	 */
	public function setItems(array $items, $useKeys = TRUE)
	{
		$flattenItems = array();
		foreach ($items as $key => $value) {
			$group = is_array($value);
			foreach ($group ? $value : array($key => $value) as $gkey => $gvalue) {
				if (!$useKeys) {
					if ($group) {
						unset($value[$gkey]);
						$value[(string) $gvalue] = $gvalue;
					}
					$gkey = (string) $gvalue;
				}

				if (isset($flattenItems[$gkey])) {
					throw new Nette\InvalidArgumentException("Items contain duplication for key '$gkey'.");
				}
				$flattenItems[$gkey] = $gvalue;
			}
			if (!$useKeys) {
				unset($items[$key]);
				$items[$group ? $key : (string) $value] = $value;
			}
		}

		$this->items = $items;
		$this->flattenItems = $flattenItems;
		return $this;
	}


	/**
	 * Returns items from which to choose.
	 * @return array
	 */
	final public function getItems()
	{
		return $this->items;
	}


	/**
	 * Returns selected value.
	 * @return string
	 */
	public function getSelectedItem()
	{
		$value = $this->getValue();
		return $value === NULL ? NULL : $this->flattenItems[$value];
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
		if (is_array($this->value)) {
			$this->value = array_diff($this->value, $value);
		} elseif (isset($this->disabled[$this->value])) {
			$this->value = NULL;
		}
		return $this;
	}


	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$selected = array_flip((array) $this->value);
		$select = parent::getControl()->setName('select');
		$option = Nette\Utils\Html::el('option');
		$items = $this->getItems();

		if ($this->prompt !== FALSE) {
			$items = array('' => $this->prompt) + $items;
		}

		foreach ($items as $group => $subitems) {
			if (!is_array($subitems)) {
				$subitems = array($group => $subitems);
				$dest = $select;
			} else {
				$dest = $select->create('optgroup')->label($this->translate($group));
			}

			foreach ($subitems as $value => $caption) {
				$option = $caption instanceof Nette\Utils\Html ? clone $caption
					: $option->setText($this->translate((string) $caption));
				$dest->add((string) $option->value($value)
					->selected(isset($selected[$value]))
					->disabled(is_array($this->disabled) ? isset($this->disabled[$value]) : FALSE)
				);
			}
		}
		return $select;
	}


	/**
	 * Performs the server side validation.
	 * @return void
	 */
	public function validate()
	{
		parent::validate();
		if (!$this->isDisabled() && $this->prompt === FALSE && $this->getValue() === NULL) {
			$this->addError(Nette\Forms\Validator::$messages[self::VALID]);
		}
	}

}
