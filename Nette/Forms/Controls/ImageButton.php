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
 * Submittable image button form control.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Forms
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
	 * Returns name of control within a Form & INamingContainer scope.
	 * @return string
	 */
	public function getHtmlName()
	{
		$name = parent::getHtmlName();
		return strpos($name, '[') === FALSE ? $name : $name . '[]';
	}



	/**
	 * Loads HTTP data.
	 * @return void
	 */
	public function loadHttpData()
	{
		$path = $this->getHtmlName(); // img_x or img['x']
		$path = explode('[', strtr(str_replace(']', '', strpos($path, '[') === FALSE ? $path . '.x' : substr($path, 0, -2)), '.', '_'));
		$this->setValue(Nette\ArrayTools::get($this->getForm()->getHttpData(), $path) !== NULL);
	}

}
