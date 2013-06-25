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
	protected $allowed = array();

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
		$this->control->setName('select');
		$this->control->size = $size > 1 ? (int) $size : NULL;
		if ($items !== NULL) {
			$this->setItems($items);
		}
	}



	/**
	 * Sets selected items (by keys).
	 * @param  string
	 * @return SelectBox  provides a fluent interface
	 */
	public function setValue($value)
	{
		if (!isset($this->allowed[$value]) && $value !== NULL) {
			throw new Nette\InvalidArgumentException("Value '$value' is out of range of current items.");
		}
		return $this->setRawValue($value);
	}



	/**
	 * Returns selected item key.
	 * @return mixed
	 */
	public function getValue()
	{
		return isset($this->allowed[$this->value]) ? $this->value : NULL;
	}



	protected function setRawValue($value)
	{
		if (is_scalar($value)) {
			$foo = array($value => NULL);
			$this->value = key($foo);
		} else {
			$this->value = NULL;
		}
		return $this;
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
	 * @return SelectBox  provides a fluent interface
	 */
	public function setPrompt($prompt)
	{
		if ($prompt === TRUE) { // back compatibility
			trigger_error(__METHOD__ . '(TRUE) is deprecated; argument must be string.', E_USER_DEPRECATED);
			$prompt = reset($this->items);
			unset($this->allowed[key($this->items)], $this->items[key($this->items)]);
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
	 * @return SelectBox  provides a fluent interface
	 */
	public function setItems(array $items, $useKeys = TRUE)
	{
		$allowed = array();
		foreach ($items as $k => $v) {
			foreach ((is_array($v) ? $v : array($k => $v)) as $key => $value) {
				if (!$useKeys) {
					if (is_array($v)) {
						unset($v[$key]);
						$v[$value] = $value;
					}
					$key = $value;
				}

				if (isset($allowed[$key])) {
					throw new Nette\InvalidArgumentException("Items contain duplication for key '$key'.");
				}

				$allowed[$key] = $value;
			}
			if (!$useKeys) {
				unset($items[$k]);
				$items[is_array($v) ? $k : $v] = $v;
			}
		}

		$this->items = $items;
		$this->allowed = $allowed;
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
		return $value === NULL ? NULL : $this->allowed[$value];
	}



	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$selected = $this->getValue();
		$selected = is_array($selected) ? array_flip($selected) : array($selected => TRUE);
		$control = parent::getControl();
		$option = Nette\Utils\Html::el('option');

		if ($this->prompt !== FALSE) {
			$control->add($this->prompt instanceof Nette\Utils\Html
				? $this->prompt->value('')
				: (string) $option->value('')->setText($this->translate((string) $this->prompt))
			);
		}

		foreach ($this->items as $key => $value) {
			if (!is_array($value)) {
				$value = array($key => $value);
				$dest = $control;
			} else {
				$dest = $control->create('optgroup')->label($this->translate($key));
			}

			foreach ($value as $key2 => $value2) {
				if ($value2 instanceof Nette\Utils\Html) {
					$dest->add((string) $value2->selected(isset($selected[$key2])));

				} else {
					$value2 = $this->translate((string) $value2);
					$dest->add((string) $option->value($key2)
						->selected(isset($selected[$key2]))
						->setText($value2));
				}
			}
		}
		return $control;
	}



	/**
	 * Performs the server side validation.
	 * @return void
	 */
	public function validate()
	{
		parent::validate();
		if ($this->prompt === FALSE && $this->getValue() === NULL) {
			$this->addError(Nette\Forms\Rules::$defaultMessages[self::VALID]);
		}
	}

}
