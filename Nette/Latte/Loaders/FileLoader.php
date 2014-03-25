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
class FileLoader extends Nette\Object implements Nette\Latte\ILoader
{

	/**
	 * Returns template source code.
	 * @return string
	 */
	public function getContent($file)
	{
		if (!is_file($file)) {
			throw new Nette\FileNotFoundException("Missing template file '$file'.");
		}
		return file_get_contents($file);
	}

}
