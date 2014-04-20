<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Latte;

use Nette,
	Latte;


/**
 * @deprecated
 */
class Engine extends Latte\Engine
{

	public function __construct()
	{
		trigger_error(__CLASS__ . ' is deprecated.', E_USER_DEPRECATED);
	}

}
