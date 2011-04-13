<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Application\UI;

use Nette;



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
