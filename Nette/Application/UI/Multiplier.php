<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
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
	/** @var callable */
	private $factory;


	/**
	 * @param callable
	 */
	public function __construct($factory)
	{
		parent::__construct();
		$this->factory = Nette\Utils\Callback::check($factory);
	}


	protected function createComponent($name)
	{
		return call_user_func($this->factory, $name, $this);
	}

}
