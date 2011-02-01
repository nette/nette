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
 * Defines methods to manipulate generic collections.
 *
 * @author     David Grudl
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
