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
 * Represents a generic collection of key/value pairs.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Collections
 * @version    $Revision$ $Date$
 */
interface IMap extends ICollection, /*::*/ArrayAccess
{
    // TODO: vyresit
    //function add($key, $value);  // produces Fatal error: Can't inherit abstract function
    function search($item);
    function getKeys();
    //function ArrayAccess::offsetSet($offset, $value);
	//function ArrayAccess::offsetGet($offset);
	//function ArrayAccess::offsetUnset($offset);
	//function ArrayAccess::offsetExists($offset);
}
