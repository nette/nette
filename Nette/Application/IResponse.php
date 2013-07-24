<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Application;

use Nette;


/**
 * Any response returned by presenter.
 *
 * @author     David Grudl
 */
interface IResponse
{

	/**
	 * Sends response to output.
	 * @return void
	 */
	function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse);

}
