<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Collections;

use Nette;



/**
 * Represents a generic collection of key/value pairs.
 *
 * @author     David Grudl
 */
interface IMap extends ICollection, \ArrayAccess
{
	function add($key, $value);
	function search($item);
	function getKeys();
	//function ArrayAccess::offsetSet($offset, $value);
	//function ArrayAccess::offsetGet($offset);
	//function ArrayAccess::offsetUnset($offset);
	//function ArrayAccess::offsetExists($offset);
}
