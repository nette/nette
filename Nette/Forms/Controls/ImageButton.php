<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
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
	 * Loads HTTP data.
	 * @return void
	 */
	public function loadHttpData()
	{
		parent::loadHttpData();
		$this->value = $this->value
			? array((int) array_shift($this->value), (int) array_shift($this->value))
			: NULL;
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
