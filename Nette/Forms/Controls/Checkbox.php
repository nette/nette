<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;


/**
 * Check box control. Allows the user to select a true or false condition.
 *
 * @author     David Grudl
 */
class Checkbox extends BaseControl
{
	/** @var Nette\Utils\Html  wrapper element template */
	private $wrapper;


	/**
	 * @param  string  label
	 */
	public function __construct($label = NULL)
	{
		parent::__construct($label);
		$this->control->type = 'checkbox';
		$this->wrapper = Nette\Utils\Html::el();
	}


	/**
	 * Sets control's value.
	 * @param  bool
	 * @return self
	 */
	public function setValue($value)
	{
		if (!is_scalar($value) && $value !== NULL) {
			throw new Nette\InvalidArgumentException(sprintf("Value must be scalar or NULL, %s given in field '%s'.", gettype($value), $this->name));
		}
		$this->value = (bool) $value;
		return $this;
	}


	/**
	 * Is control filled?
	 * @return bool
	 */
	public function isFilled()
	{
		return $this->getValue() !== FALSE; // back compatibility
	}


	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		return $this->wrapper->setHtml($this->getLabelPart()->insert(0, $this->getControlPart()));
	}


	/**
	 * Bypasses label generation.
	 * @return void
	 */
	public function getLabel($caption = NULL)
	{
		return NULL;
	}


	/**
	 * @return Nette\Utils\Html
	 */
	public function getControlPart()
	{
		return parent::getControl()->checked($this->value);
	}


	/**
	 * @return Nette\Utils\Html
	 */
	public function getLabelPart()
	{
		return parent::getLabel();
	}


	/**
	 * Returns wrapper HTML element template.
	 * @return Nette\Utils\Html
	 */
	public function getSeparatorPrototype()
	{
		return $this->wrapper;
	}

}
