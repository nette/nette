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
 * Set of radio button controls.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Forms
 */
class RadioList extends FormControl
{
	/** @var Nette::Web::Html  separator element template */
	protected $separator;

	/** @var Nette::Web::Html  container element template */
	protected $container;

	/** @var array */
	protected $items = array();



	/**
	 * @param  string  label
	 * @param  array   options from which to choose
	 */
	public function __construct($label, array $items = NULL)
	{
		parent::__construct($label);
		$this->control->type = 'radio';
		$this->container = /*Nette::Web::*/Html::el();
		$this->separator = /*Nette::Web::*/Html::el('br');
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
	 * @return Nette::Web::Html
	 */
	final public function getSeparatorPrototype()
	{
		return $this->separator;
	}



	/**
	 * Returns container HTML element template.
	 * @return Nette::Web::Html
	 */
	final public function getContainerPrototype()
	{
		return $this->container;
	}



	/**
	 * Generates control's HTML element.
	 * @return Nette::Web::Html
	 */
	public function getControl()
	{
		$container = clone $this->container;
		$separator = (string) $this->separator;
		$control = parent::getControl();
		$id = $control->id;
		$counter = 0;
		$value = $this->value === NULL ? NULL : (string) $this->getValue();
		$label = /*Nette::Web::*/Html::el('label');
		$translator = $this->getTranslator();

		foreach ($this->items as $key => $val) {
			$control->id = $label->for = $id . '-' . $counter;
			$control->checked = (string) $key === $value;
			$control->value = $key;

			if ($val instanceof /*Nette::Web::*/Html) {
				$label->setHtml($val);
			} else {
				$label->setText($translator === NULL ? $val : $translator->translate($val));
			}	

			$container->add((string) $control . (string) $label . $separator);
			$counter++;
			// TODO: separator after last item?
		}

		return $container;
	}



	/**
	 * Generates label's HTML element.
	 * @return void
	 */
	public function getLabel()
	{
		$label = parent::getLabel();
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
