<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Config;

use Nette;



/**
 * Configuration storage.
 *
 * @author     David Grudl
 */
class Config
{
	/** @var array */
	private static $extensions = array(
		'ini' => 'Nette\Config\IniAdapter',
		'neon' => 'Nette\Config\NeonAdapter',
	);



	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new Nette\StaticClassException;
	}



	/**
	 * Registers adapter for given file extension.
	 * @param  string  file extension
	 * @param  string  class name (IConfigAdapter)
	 * @return void
	 */
	public static function registerExtension($extension, $class)
	{
		if (!class_exists($class)) {
			throw new Nette\InvalidArgumentException("Class '$class' was not found.");
		}

		if (!Nette\Reflection\ClassType::from($class)->implementsInterface('Nette\Config\IAdapter')) {
			throw new Nette\InvalidArgumentException("Configuration adapter '$class' is not Nette\\Config\\IAdapter implementor.");
		}

		self::$extensions[strtolower($extension)] = $class;
	}



	/**
	 * Creates new configuration object from file.
	 * @param  string  file name
	 * @param  string  section to load
	 * @return Nette\ArrayHash
	 */
	public static function fromFile($file, $section = NULL)
	{
		$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
		if (!isset(self::$extensions[$extension])) {
			throw new Nette\InvalidArgumentException("Unknown file extension '$file'.");
		}

		$data = call_user_func(array(self::$extensions[$extension], 'load'), $file, $section);
		if ($section) {
			if (!isset($data[$section]) || !is_array($data[$section])) {
				throw new Nette\InvalidStateException("There is not section [$section] in '$file'.");
			}
			$data = $data[$section];
		}
		return Nette\ArrayHash::from($data, TRUE);
	}



	/**
	 * Save configuration to file.
	 * @param  mixed
	 * @param  string  file
	 * @return void
	 */
	public static function save($config, $file)
	{
		$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
		if (!isset(self::$extensions[$extension])) {
			throw new Nette\InvalidArgumentException("Unknown file extension '$file'.");
		}
		return call_user_func(array(self::$extensions[$extension], 'save'), $config, $file);
	}

}
