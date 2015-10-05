<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\UI;

use Nette;


/**
 * Component with ability to save and load its state.
 *
 * @author     David Grudl
 */
interface IStatePersistent
{

	/**
	 * Loads state informations.
	 * @return void
	 */
	function loadState(array $params);

	/**
	 * Saves state informations for next request.
	 * @return void
	 */
	function saveState(array & $params);

}
