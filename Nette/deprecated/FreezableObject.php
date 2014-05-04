<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette;

use Nette;


/**
 * @deprecated
 */
abstract class FreezableObject extends Object implements IFreezable
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
	public function isFrozen()
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
			$class = get_class($this);
			throw new InvalidStateException("Cannot modify a frozen object $class.");
		}
	}

}
