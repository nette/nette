<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Application;

use Nette;



/**
 *
 *
 * @author     David Grudl
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
 * @author     David Grudl
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
