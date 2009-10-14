<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Forms
 */

/*namespace Nette\Forms;*/



require_once dirname(__FILE__) . '/../../Forms/Controls/SubmitButton.php';



/**
 * Submittable image button form control.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
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
		$this->setValue(/*Nette\*/ArrayTools::get($this->getForm()->getHttpData(), $path) !== NULL);
	}

}
