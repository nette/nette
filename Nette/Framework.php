<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette;

use Nette;


/**
 * The Nette Framework (http://nette.org)
 *
 * @author     David Grudl
 */
class Framework
{

	/** Nette Framework version identification */
	const NAME = 'Nette Framework',
		VERSION = '2.2.9',
		VERSION_ID = 20209,
		REVISION = 'released on 2015-05-05';


	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new StaticClassException;
	}

}
