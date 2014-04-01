<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Latte;

use Latte;


/**
 * @deprecated
 */
class Engine extends Latte\Engine
{

	public function __invoke($s)
	{
		return $this->setLoader(new Latte\Loaders\StringLoader)->compile($s);
	}

}
