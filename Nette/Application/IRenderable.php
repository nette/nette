<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Application
 */

/*namespace Nette\Application;*/



/**
 *
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Application
 */
interface IRenderable
{

	/**
	 * Forces control to repaint.
	 * @param  string
	 * @return void
	 */
	function invalidateControl();

	/**
	 * Is required to repaint the control?
	 * @return bool
	 */
	function isControlInvalid();

}



/**
 *
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Application
 */
interface IPartiallyRenderable extends IRenderable
{

	/**
	 * Forces control or its snippet to repaint.
	 * @param  string
	 * @return void
	 */
	//function invalidateControl($snippet = NULL);

	/**
	 * Is required to repaint the control or its snippet?
	 * @param  string  snippet name
	 * @return bool
	 */
	//function isControlInvalid($snippet = NULL);

}
