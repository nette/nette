<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application\UI;

use Nette;


/**
 * Component with ability to repaint.
 *
 * @author     David Grudl
 */
interface IRenderable
{

	/**
	 * Forces control to repaint.
	 * @return void
	 */
	function redrawControl();

	/**
	 * Is required to repaint the control?
	 * @return bool
	 */
	function isControlInvalid();

}
