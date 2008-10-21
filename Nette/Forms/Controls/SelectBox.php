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



require_once dirname(__FILE__) . '/../../Forms/Controls/FormControl.php';



/**
 * Select box control that allows single item selection.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Forms
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
	public function __construct($label, array $items = NULL, $size = NULL)
	{
		parent::__construct($label);
		$this->control->setName('select');
		$this->control->size = $size > 1 ? (int) $size : NULL;
		$this->control->onmousewheel = 'return false';  // prevent accidental change
		$this->label->onclick = 'return false';  // prevent "deselect" for IE 5 - 6
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
	 * @param  bool
	 * @return SelectBox  provides a fluent interface
	 */
	public function skipFirst($value = TRUE)
	{
		$this->skipFirst = (bool) $value;
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
				if (!$this->useKeys) $key2 = $value2;
				if (isset($this->allowed[$key2])) {
					throw new /*::*/InvalidArgumentException("Items contain duplication for key '$key2'.");
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
	 * @return Nette::Web::Html
	 */
	public function getControl()
	{
		$control = parent::getControl();
		$selected = array_flip((array) $this->getValue());
		$option = /*Nette::Web::*/Html::el('option');
		$translator = $this->getTranslator();

		foreach ($this->items as $key => $value) {
			if (is_array($value)) {
				$group = $control->create('optgroup')->label($key);
				foreach ($value as $key2 => $value2) {
					if ($translator !== NULL) $value2 = $translator->translate($value2);
					if ($this->useKeys) {
						$option->value($key2)->selected(isset($selected[$key2]));
					} else {
						$option->selected(isset($selected[$value2]));
					}
					$group->add((string) $option->setText($value2));
				}
			} else {
				if ($translator !== NULL) $value = $translator->translate($value);
				if ($this->useKeys) {
					$option->value($key)->selected(isset($selected[$key]));
				} else {
					$option->selected(isset($selected[$value]));
				}
				$control->add((string) $option->setText($value));
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
