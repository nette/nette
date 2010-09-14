<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
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
