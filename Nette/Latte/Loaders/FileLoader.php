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

		} elseif (@filemtime($file) > time()) { // @ - stat may fail
			touch($file);
		}
		return file_get_contents($file);
	}


	/**
	 * @return bool
	 */
	public function isExpired($file, $time)
	{
		return @filemtime($file) > $time; // @ - stat may fail
	}


	/**
	 * Returns fully qualified template name.
	 * @return string
	 */
	public function getChildName($file, $parent = NULL)
	{
		if ($parent && !preg_match('#/|[a-z]:#iA', $file)) {
			$file = dirname($parent) . '/' . $file;
		}
		return $file;
	}

}
