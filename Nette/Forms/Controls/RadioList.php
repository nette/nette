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
 * Set of radio button controls.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Forms
 *
 * @property   array $items
 * @property-read Nette\Web\Html $separatorPrototype
 * @property-read Nette\Web\Html $containerPrototype
 */
class RadioList extends FormControl
{
	/** @var Nette\Web\Html  separator element template */
	protected $separator;

	/** @var Nette\Web\Html  container element template */
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
		$this->container = Nette\Web\Html::el();
		$this->separator = Nette\Web\Html::el('br');
		if ($items !== NULL) $this->setItems($items);
	}



	/**
	 * Returns selected radio value.
	 * @param  bool
	 * @return mixed
	 */
	public function getValue($raw = FALSE)
	{
		return is_scalar($this->value) && ($raw || isset($this->items[$this->value])) ? $this->value : NULL;
	}



	/**
	 * Sets options from which to choose.
	 * @param  array
	 * @return RadioList  provides a fluent interface
	 */
	public function setItems(array $items)
	{
		$this->items = $items;
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
	 * @return Nette\Web\Html
	 */
	final public function getSeparatorPrototype()
	{
		return $this->separator;
	}



	/**
	 * Returns container HTML element template.
	 * @return Nette\Web\Html
	 */
	final public function getContainerPrototype()
	{
		return $this->container;
	}



	/**
	 * Generates control's HTML element.
	 * @param  mixed
	 * @return Nette\Web\Html
	 */
	public function getControl($key = NULL)
	{
		if ($key === NULL) {
			$container = clone $this->container;
			$separator = (string) $this->separator;

		} elseif (!isset($this->items[$key])) {
			return NULL;
		}

		$control = parent::getControl();
		$id = $control->id;
		$counter = -1;
		$value = $this->value === NULL ? NULL : (string) $this->getValue();
		$label = Nette\Web\Html::el('label');

		foreach ($this->items as $k => $val) {
			$counter++;
			if ($key !== NULL && $key != $k) continue; // intentionally ==

			$control->id = $label->for = $id . '-' . $counter;
			$control->checked = (string) $k === $value;
			$control->value = $k;

			if ($val instanceof Nette\Web\Html) {
				$label->setHtml($val);
			} else {
				$label->setText($this->translate($val));
			}

			if ($key !== NULL) {
				return (string) $control . (string) $label;
			}

			$container->add((string) $control . (string) $label . $separator);
			// TODO: separator after last item?
		}

		return $container;
	}



	/**
	 * Generates label's HTML element.
	 * @param  string
	 * @return void
	 */
	public function getLabel($caption = NULL)
	{
		$label = parent::getLabel($caption);
		$label->for = NULL;
		return $label;
	}



	/**
	 * Filled validator: has been any radio button selected?
	 * @param  IFormControl
	 * @return bool
	 */
	public static function validateFilled(IFormControl $control)
	{
		return $control->getValue() !== NULL;
	}

}
