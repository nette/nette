<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette;

use Nette;


/**
 * The Nette Framework (https://nette.org)
 *
 * @author     David Grudl
 */
class Framework
{

	/** Nette Framework version identification */
	const NAME = 'Nette Framework',
		VERSION = '2.2.14',
		VERSION_ID = 20214,
		REVISION = 'released on 2020-08-25';


	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new StaticClassException;
	}

}
