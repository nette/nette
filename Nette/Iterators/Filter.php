<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Iterators;

use Nette;


/**
 * Callback iterator filter.
 *
 * @author     David Grudl
 */
class Filter extends \FilterIterator
{
	/** @var callable */
	private $callback;


	public function __construct(\Iterator $iterator, $callback)
	{
		parent::__construct($iterator);
		$this->callback = new Nette\Callback($callback);
	}


	public function accept()
	{
		return $this->callback->invoke($this);
	}

}
