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
 * Configuration manipulator.
 *
 * @author     David Grudl
 *
 * @property-read array $dependencies
 */
class Config extends Nette\Object
{
	/** @internal */
	const INCLUDES_KEY = 'includes',
		EXTENDS_KEY = '_extends',
		OVERWRITE = TRUE;

	private $adapters = array(
		'php' => 'Nette\Config\Adapters\PhpAdapter',
		'ini' => 'Nette\Config\Adapters\IniAdapter',
		'neon' => 'Nette\Config\Adapters\NeonAdapter',
	);

	private $dependencies = array();



	/**
	 * Static shortcut for load()
	 * @return array
	 */
	public static function fromFile($file, $section = NULL)
	{
		$loader = new static;
		return $loader->load($file, $section);
	}



	/**
	 * Reads configuration from file.
	 * @param  string  file name
	 * @param  string  optional section to load
	 * @return array
	 */
	public function load($file, $section = NULL)
	{
		if (!is_file($file) || !is_readable($file)) {
			throw new Nette\FileNotFoundException("File '$file' is missing or is not readable.");
		}
		$this->dependencies[] = $file = realpath($file);
		$data = $this->getAdapter($file)->load($file);

		if ($section) {
			$data = $this->getSection($data, $section);
		}

		// include child files
		$merged = array();
		if (isset($data[self::INCLUDES_KEY])) {
			if (!is_array($data[self::INCLUDES_KEY])) {
				throw new Nette\InvalidStateException("Invalid section 'includes' in file '$file'.");
			}
			foreach ($data[self::INCLUDES_KEY] as $include) {
				$merged = static::merge($this->load(dirname($file) . '/' . $include), $merged);
			}
		}
		unset($data[self::INCLUDES_KEY]);

		return static::merge($data, $merged);
	}



	/**
	 * Save configuration to file.
	 * @param  array
	 * @param  string  file
	 * @return void
	 */
	public function save($data, $file)
	{
		if (file_put_contents($file, $this->getAdapter($file)->dump($data)) === FALSE) {
			throw new Nette\IOException("Cannot write file '$file'.");
		}
	}



	/**
	 * Returns configuration files.
	 * @return array
	 */
	public function getDependencies()
	{
		return array_unique($this->dependencies);
	}



	/**
	 * Registers adapter for given file extension.
	 * @param  string  file extension
	 * @param  string|Nette\Config\IAdapter
	 * @return void
	 */
	public function addAdapter($extension, $adapter)
	{
		$this->adapters[strtolower($extension)] = $adapter;
	}



	/** @return IAdapter */
	private function getAdapter($file)
	{
		$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
		if (!isset($this->adapters[$extension])) {
			throw new Nette\InvalidArgumentException("Unknown file extension '$file'.");
		}
		return is_object($this->adapters[$extension]) ? $this->adapters[$extension] : new $this->adapters[$extension];
	}



	/********************* tools ****************d*g**/



	/**
	 * Merges configurations. Left has higher priority than right one.
	 * @return array
	 */
	public static function merge($left, $right)
	{
		if (is_array($left) && is_array($right)) {
			foreach ($left as $key => $val) {
				if (is_int($key)) {
					$right[] = $val;
				} else {
					if (is_array($val) && isset($val[self::EXTENDS_KEY])) {
						if ($val[self::EXTENDS_KEY] === self::OVERWRITE) {
							unset($val[self::EXTENDS_KEY]);
						}
					} elseif (isset($right[$key])) {
						$val = static::merge($val, $right[$key]);
					}
					$right[$key] = $val;
				}
			}
			return $right;

		} elseif ($left === NULL && is_array($right)) {
			return $right;

		} else {
			return $left;
		}
	}



	/**
	 * Finds out and removes information about the parent.
	 * @return mixed
	 */
	public static function takeParent(& $data)
	{
		if (is_array($data) && isset($data[self::EXTENDS_KEY])) {
			$parent = $data[self::EXTENDS_KEY];
			unset($data[self::EXTENDS_KEY]);
			return $parent;
		}
	}



	/**
	 * @return bool
	 */
	public static function isOverwriting(& $data)
	{
		return is_array($data) && isset($data[self::EXTENDS_KEY]) && $data[self::EXTENDS_KEY] === self::OVERWRITE;
	}



	/**
	 * @return bool
	 */
	public static function isInheriting(& $data)
	{
		return is_array($data) && isset($data[self::EXTENDS_KEY]) && $data[self::EXTENDS_KEY] !== self::OVERWRITE;
	}



	private function getSection(array $data, $key)
	{
		if (!array_key_exists($key, $data) || !is_array($data[$key]) && $data[$key] !== NULL) {
			throw new Nette\InvalidStateException("Section '$key' is missing or is not an array.");
		}
		$item = $data[$key];
		if ($parent = static::takeParent($item)) {
			$item = static::merge($item, $this->getSection($data, $parent));
		}
		return $item;
	}

}
