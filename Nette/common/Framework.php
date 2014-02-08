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
		VERSION = '2.1.1',
		VERSION_ID = 20101,
		REVISION = 'released on 2014-02-08';


	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new StaticClassException;
	}

}
