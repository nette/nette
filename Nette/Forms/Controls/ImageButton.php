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
 * Submittable image button form control.
 *
 * @author     David Grudl
 */
class ImageButton extends SubmitButton
{

	/**
	 * @param  string  URI of the image
	 * @param  string  alternate text for the image
	 */
	public function __construct($src = NULL, $alt = NULL)
	{
		parent::__construct();
		$this->control->type = 'image';
		$this->control->src = $src;
		$this->control->alt = $alt;
	}


	/**
	 * Sets coordinates as a value if available.
	 * @param  bool|array
	 * @return ImageButton  provides a fluent interface
	 */
	public function setValue($value)
	{
		parent::setValue($value);
		if (is_array($value) && isset($value[0], $value[1])) {
			$this->value = array((int) $value[0], (int) $value[1]);
		}
		return $this;
	}


	/**
	 * Returns HTML name of control.
	 * @return string
	 */
	public function getHtmlName()
	{
		return parent::getHtmlName() . '[]';
	}

}
