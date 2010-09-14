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
