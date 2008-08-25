<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette::Config
 * @version    $Id$
 */

/*namespace Nette::Config;*/



require_once dirname(__FILE__) . '/../Config/IConfigAdapter.php';



/**
 * Reading and writing XML files.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Config
 */
final class ConfigAdapterXml implements IConfigAdapter
{

	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new /*::*/LogicException("Cannot instantiate static class " . get_class($this));
	}



	/**
	 * Reads configuration from XML file.
	 * @param  string  file name
	 * @param  string  section to load
	 * @return array
	 */
	public static function load($file, $section = NULL)
	{
		throw new /*::*/NotImplementedException;

		if (!is_file($file) || !is_readable($file)) {
			throw new /*::*/FileNotFoundException("File '$file' is missing or is not readable.");
		}

		$data = new SimpleXMLElement($file, NULL, TRUE);

		foreach ($data as $secName => $secData) {
			if ($secData['extends']) {
				// $data[$child] = $secData;
			}
		}

		return $data;
	}



	/**
	 * Write XML file.
	 * @param  Config to save
	 * @param  string  file
	 * @return void
	 */
	public static function save($config, $file, $section = NULL)
	{
		throw new /*::*/NotImplementedException;
	}

}
