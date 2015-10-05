<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\UI;

use Nette;


/**
 * Component with ability to receive signal.
 *
 * @author     David Grudl
 */
interface ISignalReceiver
{

	/**
	 * @param  string
	 * @return void
	 */
	function signalReceived($signal); // handleSignal

}
