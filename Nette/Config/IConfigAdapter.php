<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Config;

use Nette;



/**
 * Adapter for reading and writing configuration files.
 *
 * @author     David Grudl
 */
interface IConfigAdapter
{

	/**
	 * Reads configuration from file.
	 * @param  string  file name
	 * @param  string  section to load
	 * @return array
	 */
	static function load($file, $section = NULL);

	/**
	 * Writes configuration to file.
	 * @param  Config to save
	 * @param  string  file
	 * @param  string  section name
	 * @return void
	 */
	static function save($config, $file, $section = NULL);

}
