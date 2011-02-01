<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette;

use Nette;



/**
 * Defines an object that has a modifiable state and a read-only (frozen) state.
 *
 * @author     David Grudl
 *
 * @property-read bool $frozen
 */
abstract class FreezableObject extends Object
{
	/** @var bool */
	private $frozen = FALSE;



	/**
	 * Makes the object unmodifiable.
	 * @return void
	 */
	public function freeze()
	{
		$this->frozen = TRUE;
	}



	/**
	 * Is the object unmodifiable?
	 * @return bool
	 */
	final public function isFrozen()
	{
		return $this->frozen;
	}



	/**
	 * Creates a modifiable clone of the object.
	 * @return void
	 */
	public function __clone()
	{
		$this->frozen = FALSE;
	}



	/**
	 * @return void
	 */
	protected function updating()
	{
		if ($this->frozen) {
			throw new \InvalidStateException("Cannot modify a frozen object {$this->reflection->name}.");
		}
	}

}
