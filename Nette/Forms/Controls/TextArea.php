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
 * Multiline text input control.
 *
 * @author     David Grudl
 */
class TextArea extends TextBase
{

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
			->setName('textarea')
			->setText($value);
	}

}
