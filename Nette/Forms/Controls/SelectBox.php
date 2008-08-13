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
 * Select box control that allows single or multiple item selection.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Forms
 */
class SelectBox extends FormControl
{
	/** @var array */
	private $items;

	/** @var array */
	protected $allowed;

	/** @var array */
	protected $multiple;

	/** @var bool */
	protected $skipFirst = FALSE;



	/**
	 * @param  string  label
	 * @param  array   items from which to choose
	 * @param  int     number of rows that should be visible
	 */
	public function __construct($label, array $items, $multiple = FALSE, $size = NULL)
	{
		parent::__construct($label);
		$this->control->setName('select');
		$this->control->multiple = (bool) $multiple;
		$this->control->size = $size > 1 ? (int) $size : NULL;
		$this->control->onmousewheel = 'return false';  // prevent accidental change
		$this->label->onclick = 'return false';  // prevent "deselect" for IE 5 - 6

		$this->items = $items;
		$this->multiple = $multiple;
		$this->value = NULL;
		$this->allowed = array();

		foreach ($items as $key => $value) {
			if (is_array($value)) {
				foreach ($value as $key2 => $value2) {
					$this->allowed[$key2] = TRUE;
				}
			} else {
				$this->allowed[$key] = TRUE;
			}
		}
	}



	/**
	 * Sets selected item/items.
	 * @param  string|int|array
	 * @return void
	 */
	public function setValue($value)
	{
		$allowed = $this->allowed;
		if ($this->skipFirst) {
			$allowed = array_slice($allowed, 1, count($allowed), TRUE);
		}

		if ($this->multiple) {
			$this->value = array();
			foreach ((array) $value as $val) {
				if (isset($allowed[$val])) {
					$this->value[] = $val;
				}
			}
		} else {
			$this->value = isset($allowed[$value]) ? $value : NULL;
		}
	}



	/**
	 * Ignores the first item in select box.
	 * @param  bool
	 * @return SelectBox|bool  provides a fluent interface or returns current value
	 */
	public function skipFirst($value = NULL)
	{
		if ($value === NULL) {
			return $this->skipFirst;
		}
		$this->skipFirst = (bool) $value;
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
	 * Generates control's HTML element.
	 * @return Nette::Web::Html
	 */
	public function getControl()
	{
		$control = parent::getControl();
		if ($this->multiple) $control->name .= '[]';
		$selected = array_flip((array) $this->value);
		$option = /*Nette::Web::*/Html::el('option');
		$translator = $this->getTranslator();

		foreach ($this->items as $key => $value) {
			if (is_array($value)) {
				$group = $control->create('optgroup')->label($key);
				foreach ($value as $key2 => $value2) {
					if ($translator !== NULL) $value2 = $translator->translate($value2);
					$option->value($key2)->selected(isset($selected[$key2]))->setText($value2);
					$group->add((string) $option);
				}
			} else {
				if ($translator !== NULL) $value = $translator->translate($value);
				$option->value($key)->selected(isset($selected[$key]))->setText($value);
				$control->add((string) $option);
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
