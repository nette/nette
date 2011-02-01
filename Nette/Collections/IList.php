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
 * Represents a collection of objects that can be individually
 * accessed by index (ordered collection)
 *
 * @author     David Grudl
 */
interface IList extends ICollection, \ArrayAccess
{
	function indexOf($item);
	function insertAt($index, $item);
	//function ArrayAccess::offsetSet($offset, $value);
	//function ArrayAccess::offsetGet($offset);
	//function ArrayAccess::offsetUnset($offset);
	//function ArrayAccess::offsetExists($offset);
}
