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
	 * Sets selected radio value.
	 * @param  string
	 * @return self
	 */
	public function setValue($value)
	{
		if ($value !== NULL && !isset($this->items[(string) $value])) {
			throw new Nette\InvalidArgumentException("Value '$value' is out of range of current items.");
		}
		$this->value = $value === NULL ? NULL : key(array((string) $value => NULL));
		return $this;
	}


	/**
	 * Returns selected radio value.
	 * @return mixed
	 */
	public function getValue($raw = FALSE)
	{
		if ($raw) {
			trigger_error(__METHOD__ . '(TRUE) is deprecated; use getRawValue() instead.', E_USER_DEPRECATED);
			return $this->getRawValue();
		}
		return isset($this->items[$this->value]) ? $this->value : NULL;
	}


	/**
	 * Returns selected radio value (not checked).
	 * @return mixed
	 */
	public function getRawValue()
	{
		return $this->value;
	}


	/**
	 * Is any radio button selected?
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
	 * @return self
	 */
	public function setItems(array $items, $useKeys = TRUE)
	{
		if (!$useKeys) {
			$items = array_combine($items, $items);
		}
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
		if (isset($this->disabled[$this->value])) {
			$this->value = NULL;
		}
		return $this;
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
		$selected = array_flip((array) $this->value);
		$input = parent::getControl();

		if ($key !== NULL) {
			return $input->addAttributes(array(
				'type' => 'radio',
				'id' => $this->getHtmlId() . "-$key",
				'checked' => isset($selected[$key]),
				'value' => $key,
			));
		}

		$idBase = $input->id;
		$container = clone $this->container;
		$separator = (string) $this->separator;
		$label = parent::getLabel();

		foreach ($this->items as $value => $caption) {
			$input->id = $label->for = $idBase . '-' . $value;
			$input->checked(isset($selected[$value]))
				->disabled(is_array($this->disabled) ? isset($this->disabled[$value]) : $this->disabled)
				->value($value);
			$label->setText($this->translate($caption));

			$container->add($label->insert(0, $input) . $separator);
			unset($input->attrs['data-nette-rules']);
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
