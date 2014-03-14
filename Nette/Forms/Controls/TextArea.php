<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;


/**
 * Multiline text input control.
 *
 * @author     David Grudl
 */
class TextArea extends TextBase
{

	/**
	 * @param  string  label
	 */
	public function __construct($label = NULL)
	{
		parent::__construct($label);
		$this->control->setName('textarea');
	}


	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$value = $this->getValue();
		if ($value === '') {
			$value = $this->translate($this->emptyValue);
		}
		return parent::getControl()
			->setText($value);
	}

}
