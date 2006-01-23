<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com/
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com/
 * @category   Nette
 * @package    Nette::Collections
 */

/*namespace Nette::Collections;*/



/**
 * Represents a collection of objects that can be individually.
 * accessed by index (ordered collection)
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Collections
 * @version    $Revision$ $Date$
 */
interface IList extends ICollection, /*::*/ArrayAccess
{
    function indexOf($item);
    function insertAt($index, $item);
    //function ArrayAccess::offsetSet($offset, $value);
	//function ArrayAccess::offsetGet($offset);
	//function ArrayAccess::offsetUnset($offset);
	//function ArrayAccess::offsetExists($offset);
}
