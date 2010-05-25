<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Application
 */

/*namespace Nette\Application;*/



/**
 * Component with ability to save and load its state.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Application
 */
interface IStatePersistent
{

	/**
	 * Loads state informations.
	 * @param  array
	 * @return void
	 */
	function loadState(array $params);

	/**
	 * Saves state informations for next request.
	 * @param  array
	 * @return void
	 */
	function saveState(array & $params);

}
