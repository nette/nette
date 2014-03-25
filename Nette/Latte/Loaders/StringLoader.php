<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Latte\Loaders;

use Nette;


/**
 * Template loader.
 *
 * @author     David Grudl
 */
class StringLoader extends Nette\Object implements Nette\Latte\ILoader
{

	/**
	 * Returns template source code.
	 * @return string
	 */
	public function getContent($content)
	{
		return $content;
	}

}
