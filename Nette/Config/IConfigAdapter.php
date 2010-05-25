<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Config
 */

/*namespace Nette\Config;*/



/**
 * Adapter for reading and writing configuration files.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Config
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
