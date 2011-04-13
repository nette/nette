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
class Config implements \ArrayAccess, \IteratorAggregate
{
	/** @var array */
	private static $extensions = array(
		'ini' => 'Nette\Config\IniAdapter',
		'neon' => 'Nette\Config\NeonAdapter',
	);



	/**
	 * Registers adapter for given file extension.
	 * @param  string  file extension
	 * @param  string  class name (IConfigAdapter)
	 * @return void
	 */
	public static function registerExtension($extension, $class)
	{
		if (!class_exists($class)) {
			throw new \InvalidArgumentException("Class '$class' was not found.");
		}

		if (!Nette\Reflection\ClassType::from($class)->implementsInterface('Nette\Config\IAdapter')) {
			throw new \InvalidArgumentException("Configuration adapter '$class' is not Nette\\Config\\IAdapter implementor.");
		}

		self::$extensions[strtolower($extension)] = $class;
	}



	/**
	 * Creates new configuration object from file.
	 * @param  string  file name
	 * @param  string  section to load
	 * @return Config
	 */
	public static function fromFile($file, $section = NULL)
	{
		$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
		if (!isset(self::$extensions[$extension])) {
			throw new \InvalidArgumentException("Unknown file extension '$file'.");
		}

		$data = call_user_func(array(self::$extensions[$extension], 'load'), $file, $section);
		if ($section) {
			if (!isset($data[$section]) || !is_array($data[$section])) {
				throw new Nette\InvalidStateException("There is not section [$section] in '$file'.");
			}
			$data = $data[$section];
		}
		return new static($data);
	}



	/**
	 * @param  array to wrap
	 */
	public function __construct($arr = NULL)
	{
		foreach ((array) $arr as $k => $v) {
			$this->$k = is_array($v) ? new static($v) : $v;
		}
	}



	/**
	 * Save configuration to file.
	 * @param  string  file
	 * @return void
	 */
	public function save($file)
	{
		$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
		if (!isset(self::$extensions[$extension])) {
			throw new \InvalidArgumentException("Unknown file extension '$file'.");
		}
		return call_user_func(array(self::$extensions[$extension], 'save'), $this, $file);
	}



	/********************* data access ****************d*g**/



	public function __set($key, $value)
	{
		if (!is_scalar($key)) {
			throw new \InvalidArgumentException("Key must be either a string or an integer.");

		} elseif ($value === NULL) {
			unset($this->$key);

		} else {
			$this->$key = $value;
		}
	}



	public function &__get($key)
	{
		if (!is_scalar($key)) {
			throw new \InvalidArgumentException("Key must be either a string or an integer.");
		}
		return $this->$key;
	}



	public function __isset($key)
	{
		return FALSE;
	}



	public function __unset($key)
	{
	}



	/**
	 * Replaces or appends a item.
	 * @param  mixed
	 * @param  mixed
	 * @return void
	 */
	public function offsetSet($key, $value)
	{
		$this->__set($key, $value);
	}



	/**
	 * Returns a item.
	 * @param  mixed
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		if (!is_scalar($key)) {
			throw new \InvalidArgumentException("Key must be either a string or an integer.");

		} elseif (!isset($this->$key)) {
			return NULL;
		}
		return $this->$key;
	}



	/**
	 * Determines whether a item exists.
	 * @param  mixed
	 * @return bool
	 */
	public function offsetExists($key)
	{
		if (!is_scalar($key)) {
			throw new \InvalidArgumentException("Key must be either a string or an integer.");
		}
		return isset($this->$key);
	}



	/**
	 * Removes a item.
	 * @param  mixed
	 * @return void
	 */
	public function offsetUnset($key)
	{
		if (!is_scalar($key)) {
			throw new \InvalidArgumentException("Key must be either a string or an integer.");
		}
		unset($this->$key);
	}



	/**
	 * Returns an iterator over all items.
	 * @return \RecursiveIterator
	 */
	public function getIterator()
	{
		return new Nette\Iterators\Recursor(new \ArrayIterator($this));
	}



	/**
	 * @return array
	 */
	public function toArray()
	{
		$arr = array();
		foreach ($this as $k => $v) {
			$arr[$k] = $v instanceof self ? $v->toArray() : $v;
		}
		return $arr;
	}

}
