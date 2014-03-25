<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Latte;

use Nette;


/**
 * Template loader.
 */
interface ILoader
{

	/**
	 * Returns template source code.
	 * @return string
	 */
	function getContent($name);

}
