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

use Nette;



/**
 * Hidden form control used to store a non-displayed value.
 *
 * @author     David Grudl
 */
class HiddenField extends BaseControl
{

	public function __construct()
	{
		if (func_num_args()) {
			throw new Nette\DeprecatedException('The "forced value" has been deprecated.');
		}
		parent::__construct();
		$this->control->type = 'hidden';
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
	 * Sets control's value.
	 * @param  string
	 * @return HiddenField  provides a fluent interface
	 */
	public function setValue($value)
	{
		if (!is_scalar($value) && $value !== NULL) {
			throw new Nette\InvalidArgumentException('Value must be scalar or NULL, ' . gettype($value) . ' given.');
		}
		$this->value = (string) $value;
		return $this;
	}



	protected function setRawValue($value)
	{
		return $this->setValue(is_scalar($value) ? (string) $value : '');
	}



	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		return parent::getControl()
			->value($this->value)
			->data('nette-rules', NULL);
	}



	/**
	 * Adds error message to the list.
	 * @param  string  error message
	 * @return void
	 */
	public function addError($message)
	{
		$this->getForm()->addError($message);
	}

}
