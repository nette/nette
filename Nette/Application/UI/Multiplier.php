<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application\UI;

use Nette;


/**
 * Component multiplier.
 *
 * @author     David Grudl
 */
class Multiplier extends PresenterComponent
{
	/** @var Nette\Callback */
	private $factory;


	/**
	 * @param callable
	 */
	public function __construct($factory)
	{
		parent::__construct();
		$this->factory = new Nette\Callback($factory);
	}


	protected function createComponent($name)
	{
		return $this->factory->invoke($name, $this);
	}

}
