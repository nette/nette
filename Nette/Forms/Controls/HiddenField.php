<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;


/**
 * Hidden form control used to store a non-displayed value.
 *
 * @author     David Grudl
 */
class HiddenField extends BaseControl
{
	/** @var string */
	private $forcedValue;


	public function __construct($forcedValue = NULL)
	{
		parent::__construct();
		$this->control->type = 'hidden';
		$this->value = (string) $forcedValue;
		$this->forcedValue = $forcedValue;
	}


	/**
	 * Sets control's value.
	 * @param  string
	 * @return self
	 */
	public function setValue($value)
	{
		$this->value = is_scalar($value) ? (string) $value : '';
		return $this;
	}


	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		return parent::getControl()
			->value($this->forcedValue === NULL ? $this->value : $this->forcedValue)
			->data('nette-rules', NULL);
	}


	/**
	 * Bypasses label generation.
	 * @return void
	 */
	public function getLabel($caption = NULL)
	{
		return NULL;
	}

}
