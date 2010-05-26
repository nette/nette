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

namespace Nette\Application;

use Nette;



/**
 * Any response returned by presenter.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Application
 */
interface IPresenterResponse
{

	/**
	 * Sends response to output.
	 * @return void
	 */
	function send();

}
