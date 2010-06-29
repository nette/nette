<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette
 */

namespace Nette;

use Nette;



/**
 * Custom output for Nette\Debug.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette
 */
interface IDebuggable
{

	/**
	 * Returns custom panels.
	 * @return array
	 */
	function getPanels();

}
