<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Collections
 */

namespace Nette\Collections;

use Nette;



/**
 * Represents a collection of objects that can be individually
 * accessed by index (ordered collection)
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Collections
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
