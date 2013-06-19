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

use Nette,
	Nette\Utils\Html;



/**
 * Set of radio button controls.
 *
 * @author     David Grudl
 *
 * @property   array $items
 * @property-read Nette\Utils\Html $separatorPrototype
 * @property-read Nette\Utils\Html $containerPrototype
 */
class RadioList extends BaseControl
{
	/** @var Nette\Utils\Html  separator element template */
	protected $separator;

	/** @var Nette\Utils\Html  container element template */
	protected $container;

	/** @var array */
	protected $items = array();



	/**
	 * @param  string  label
	 * @param  array   options from which to choose
	 */
	public function __construct($label = NULL, array $items = NULL)
	{
		parent::__construct($label);
		$this->control->type = 'radio';
		$this->container = Html::el();
		$this->separator = Html::el('br');
		if ($items !== NULL) {
			$this->setItems($items);
		}
	}



	/**
	 * Returns selected radio value.
	 * @return mixed
	 */
	public function getValue($raw = FALSE)
	{
		if ($raw) {
			trigger_error(__METHOD__ . '(TRUE) is deprecated; use getRawValue() instead.', E_USER_DEPRECATED);
		}
		$value = $this->getRawValue();
		return ($raw || isset($this->items[$value])) ? $value : NULL;
	}



	/**
	 * Returns selected radio value (not checked).
	 * @return mixed
	 */
	public function getRawValue()
	{
		if (is_scalar($this->value)) {
			$foo = array($this->value => NULL);
			return key($foo);
		}
	}



	/**
	 * Has been any radio button selected?
	 * @return bool
	 */
	public function isFilled()
	{
		return $this->getValue() !== NULL;
	}



	/**
	 * Sets options from which to choose.
	 * @param  array
	 * @param  bool
	 * @return RadioList  provides a fluent interface
	 */
	public function setItems(array $items, $useKeys = TRUE)
	{
		$this->items = $useKeys ? $items : array_combine($items, $items);
		return $this;
	}



	/**
	 * Returns options from which to choose.
	 * @return array
	 */
	final public function getItems()
	{
		return $this->items;
	}



	/**
	 * Returns separator HTML element template.
	 * @return Nette\Utils\Html
	 */
	final public function getSeparatorPrototype()
	{
		return $this->separator;
	}



	/**
	 * Returns container HTML element template.
	 * @return Nette\Utils\Html
	 */
	final public function getContainerPrototype()
	{
		return $this->container;
	}



	/**
	 * Generates control's HTML element.
	 * @param  mixed
	 * @return Nette\Utils\Html
	 */
	public function getControl($key = NULL)
	{
		$value = $this->value === NULL ? NULL : (string) $this->getValue();
		$control = parent::getControl();

		if ($key !== NULL) {
			$control->id .= '-' . $key;
			$control->checked = (string) $key === $value;
			$control->value = $key;
			return $control;
		}

		$id = $control->id;
		$container = clone $this->container;
		$separator = (string) $this->separator;
		$label = $this->getLabel();

		foreach ($this->items as $k => $val) {
			$control->id = $label->for = $id . '-' . $k;
			$control->checked = (string) $k === $value;
			$control->value = $k;
			$label->setText($this->translate($val));

			$container->add((string) $control . (string) $label . $separator);
			$control->data('nette-rules', NULL);
			// TODO: separator after last item?
		}

		return $container;
	}



	/**
	 * Generates label's HTML element.
	 * @param  string
	 * @param  mixed
	 * @return void
	 */
	public function getLabel($caption = NULL, $key = NULL)
	{
		if ($key === NULL) {
			$label = parent::getLabel($caption);
			$label->for = NULL;
		} else {
			$label = parent::getLabel($caption === NULL ? $this->items[$key] : $caption);
			$label->for .= '-' . $key;
		}
		return $label;
	}

}
