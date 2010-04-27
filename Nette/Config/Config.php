<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Config
 */

/*namespace Nette\Config;*/



/**
 * Configuration storage.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Config
 */
class Config extends /*\*/ArrayObject
{
	/** @var array */
	private static $extensions = array(
		'ini' => /*Nette\Config\*/'ConfigAdapterIni',
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
			throw new /*\*/InvalidArgumentException("Class '$class' was not found.");
		}

		if (!/*Nette\Reflection\*/ClassReflection::from($class)->implementsInterface(/*Nette\Config\*/'IConfigAdapter')) {
			throw new /*\*/InvalidArgumentException("Configuration adapter '$class' is not Nette\\Config\\IConfigAdapter implementor.");
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
		if (isset(self::$extensions[$extension])) {
			$arr = call_user_func(array(self::$extensions[$extension], 'load'), $file, $section);
			return new /**/self/**//*static*/($arr);

		} else {
			throw new /*\*/InvalidArgumentException("Unknown file extension '$file'.");
		}
	}



	/**
	 * @param  array to wrap
	 */
	public function __construct($arr = NULL)
	{
		if ($arr) {
			$this->import($arr);
		}
	}



	/**
	 * Save configuration to file.
	 * @param  string  file
	 * @param  string  section to write
	 * @return void
	 */
	public function save($file, $section = NULL)
	{
		$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
		if (isset(self::$extensions[$extension])) {
			return call_user_func(array(self::$extensions[$extension], 'save'), $this, $file, $section);

		} else {
			throw new /*\*/InvalidArgumentException("Unknown file extension '$file'.");
		}
	}



	/********************* data access ****************d*g**/



	/**
	 * Expand all variables.
	 * @return void
	 */
	public function expand()
	{
		$data = $this->getArrayCopy();
		foreach ($data as $key => $val) {
			if (is_string($val)) {
				$data[$key] = /*Nette\*/Environment::expand($val);
			} elseif ($val instanceof self) {
				$val->expand();
			}
		}
		$this->exchangeArray($data);
	}



	/**
	 * Import from array or any traversable object.
	 * @param  array|\Traversable
	 * @return void
	 * @throws \InvalidArgumentException
	 */
	public function import($arr)
	{
		foreach ($arr as $key => $val) {
			if (is_array($val)) {
				$arr[$key] = $obj = clone $this;
				$obj->import($val);
			}
		}
		$this->exchangeArray($arr);
	}



	/**
	 * Returns an array containing all of the elements in this collection.
	 * @return array
	 */
	public function toArray()
	{
		$res = $this->getArrayCopy();
		foreach ($res as $key => $val) {
			if ($val instanceof self) {
				$res[$key] = $val->toArray();
			}
		}
		return $res;
	}



	/**
	 * Creates a modifiable clone of the object.
	 * @return void
	 */
	public function __clone()
	{
		$data = $this->getArrayCopy();
		foreach ($data as $key => $val) {
			if ($val instanceof self) {
				$data[$key] = clone $val;
			}
		}
		$this->exchangeArray($data);
	}



	/********************* data access via overloading ****************d*g**/



	/**
	 * Returns item. Do not call directly.
	 * @param  int index
	 * @return mixed
	 */
	public function &__get($key)
	{
		$val = $this->offsetExists($key) ? $this->offsetGet($key) : NULL;
		return $val;
	}



	/**
	 * Inserts (replaces) item. Do not call directly.
	 * @param  int index
	 * @param  object
	 * @return void
	 */
	public function __set($key, $item)
	{
		$this->offsetSet($key, $item);
	}



	/**
	 * Exists item?
	 * @param  string  name
	 * @return bool
	 */
	public function __isset($key)
	{
		return $this->offsetExists($key);
	}



	/**
	 * Removes the element at the specified position in this list.
	 * @param  string  name
	 * @return void
	 */
	public function __unset($key)
	{
		$this->offsetUnset($key);
	}



	public function getIterator()
	{
		return new /*\*/ArrayIterator($this->getArrayCopy());
	}

}
