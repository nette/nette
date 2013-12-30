<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette;

use Nette;


/**
 * Object that has a modifiable and a read-only (frozen) state.
 *
 * @author     David Grudl
 */
interface IFreezable
{

	/**
	 * Makes the object unmodifiable.
	 * @return void
	 */
	function freeze();

	/**
	 * Is the object unmodifiable?
	 * @return bool
	 */
	function isFrozen();

}
