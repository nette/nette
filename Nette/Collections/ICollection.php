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
 * Defines methods to manipulate generic collections.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Collections
 */
interface ICollection extends \Countable, \IteratorAggregate
{
	function append($item);
	function remove($item);
	function clear();
	function contains($item);
	//function IteratorAggregate::getIterator();
	//function Countable::count();
}
