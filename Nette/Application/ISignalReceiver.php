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
