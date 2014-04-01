<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Bridges\ApplicationLatte;

use Nette,
	Latte;


/**
 * Template loader.
 *
 * @author     David Grudl
 */
class Loader extends Latte\Loaders\FileLoader
{
	/** @var Nette\Application\UI\Presenter */
	private $presenter;


	public function __construct(Nette\Application\UI\Presenter $presenter)
	{
		$this->presenter = $presenter;
	}

}
