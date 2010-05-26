<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Forms
 */

namespace Nette\Forms;

use Nette;



/**
 * Select box control that allows single item selection.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Forms
 *
 * @property-read mixed $rawValue
 * @property   array $items
 * @property-read mixed $selectedItem
 * @property-read bool $firstSkipped
 */
class SelectBox extends FormControl
{
	/** @var array */
	private $items = array();

	/** @var array */
	protected $allowed = array();

	/** @var bool */
	private $skipFirst = FALSE;

	/** @var bool */
	private $useKeys = TRUE;



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
		$this->control->onfocus = 'this.onmousewheel=function(){return false}';  // prevents accidental change in IE
		$this->label->onclick = 'document.getElementById(this.htmlFor).focus();return false';  // prevents deselect in IE 5 - 6
		if ($items !== NULL) {
			$this->setItems($items);
		}
	}



	/**
	 * Returns selected item key.
	 * @return mixed
	 */
	public function getValue()
	{
		$allowed = $this->allowed;
		if ($this->skipFirst) {
			$allowed = array_slice($allowed, 1, count($allowed), TRUE);
		}

		return is_scalar($this->value) && isset($allowed[$this->value]) ? $this->value : NULL;
	}



	/**
	 * Returns selected item key (not checked).
	 * @return mixed
	 */
	public function getRawValue()
	{
		return is_scalar($this->value) ? $this->value : NULL;
	}



	/**
	 * Ignores the first item in select box.
	 * @param  string
	 * @return SelectBox  provides a fluent interface
	 */
	public function skipFirst($item = NULL)
	{
		if (is_bool($item)) {
			$this->skipFirst = $item;
		} else {
			$this->skipFirst = TRUE;
			if ($item !== NULL) {
				$this->items = array('' => $item) + $this->items;
				$this->allowed = array('' => '') + $this->allowed;
			}
		}
		return $this;
	}



	/**
	 * Is first item in select box ignored?
	 * @return bool
	 */
	final public function isFirstSkipped()
	{
		return $this->skipFirst;
	}



	/**
	 * Are the keys used?
	 * @return bool
	 */
	final public function areKeysUsed()
	{
		return $this->useKeys;
	}



	/**
	 * Sets items from which to choose.
	 * @param  array
	 * @return SelectBox  provides a fluent interface
	 */
	public function setItems(array $items, $useKeys = TRUE)
	{
		$this->items = $items;
		$this->allowed = array();
		$this->useKeys = (bool) $useKeys;

		foreach ($items as $key => $value) {
			if (!is_array($value)) {
				$value = array($key => $value);
			}

			foreach ($value as $key2 => $value2) {
				if (!$this->useKeys) {
					if (!is_scalar($value2)) {
						throw new \InvalidArgumentException("All items must be scalars.");
					}
					$key2 = $value2;
				}

				if (isset($this->allowed[$key2])) {
					throw new \InvalidArgumentException("Items contain duplication for key '$key2'.");
				}

				$this->allowed[$key2] = $value2;
			}
		}
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
		if (!$this->useKeys) {
			return $this->getValue();

		} else {
			$value = $this->getValue();
			return $value === NULL ? NULL : $this->allowed[$value];
		}
	}



	/**
	 * Generates control's HTML element.
	 * @return Nette\Web\Html
	 */
	public function getControl()
	{
		$control = parent::getControl();
		$selected = $this->getValue();
		$selected = is_array($selected) ? array_flip($selected) : array($selected => TRUE);
		$option = Nette\Web\Html::el('option');

		foreach ($this->items as $key => $value) {
			if (!is_array($value)) {
				$value = array($key => $value);
				$dest = $control;

			} else {
				$dest = $control->create('optgroup')->label($key);
			}

			foreach ($value as $key2 => $value2) {
				if ($value2 instanceof Nette\Web\Html) {
					$dest->add((string) $value2->selected(isset($selected[$key2])));

				} elseif ($this->useKeys) {
					$dest->add((string) $option->value($key2)->selected(isset($selected[$key2]))->setText($this->translate($value2)));

				} else {
					$dest->add((string) $option->selected(isset($selected[$value2]))->setText($this->translate($value2)));
				}
			}
		}
		return $control;
	}



	/**
	 * Filled validator: has been any item selected?
	 * @param  IFormControl
	 * @return bool
	 */
	public static function validateFilled(IFormControl $control)
	{
		$value = $control->getValue();
		return is_array($value) ? count($value) > 0 : $value !== NULL;
	}

}
