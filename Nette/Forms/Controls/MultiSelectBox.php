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
 * Select box control that allows multiple item selection.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Forms
 */
class MultiSelectBox extends SelectBox
{


	/**
	 * Returns selected keys.
	 * @return array
	 */
	public function getValue()
	{
		$allowed = array_keys($this->allowed);
		if ($this->isFirstSkipped()) {
			unset($allowed[0]);
		}
		return array_intersect($this->getRawValue(), $allowed);
	}



	/**
	 * Returns selected keys (not checked).
	 * @return array
	 */
	public function getRawValue()
	{
		if (is_scalar($this->value)) {
			$value = array($this->value);

		} elseif (!is_array($this->value)) {
			$value = array();

		} else {
			$value = $this->value;
		}

		$res = array();
		foreach ($value as $val) {
			if (is_scalar($val)) {
				$res[] = $val;
			}
		}
		return $res;
	}



	/**
	 * Returns selected values.
	 * @return array
	 */
	public function getSelectedItem()
	{
		if (!$this->areKeysUsed()) {
			return $this->getValue();

		} else {
			$res = array();
			foreach ($this->getValue() as $value) {
				$res[$value] = $this->allowed[$value];
			}
			return $res;
		}
	}



	/**
	 * Returns name of control within a Form & INamingContainer scope.
	 * @return string
	 */
	public function getHtmlName()
	{
		return parent::getHtmlName() . '[]';
	}



	/**
	 * Generates control's HTML element.
	 * @return Nette\Web\Html
	 */
	public function getControl()
	{
		$control = parent::getControl();
		$control->multiple = TRUE;
		return $control;
	}

}
