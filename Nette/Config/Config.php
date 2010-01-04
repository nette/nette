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
class Config extends /*Nette\Collections\*/Hashtable
{
	/**#@+ flag */
	const READONLY = 1;
	const EXPAND = 2;
	/**#@-*/

	/** @var array */
	private static $extensions = array(
		'ini' => /*Nette\Config\*/'ConfigAdapterIni',
		'xml' => /*Nette\Config\*/'ConfigAdapterXml',
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

		if (!/*Nette\Reflection\*/ClassReflection::create($class)->implementsInterface(/*Nette\Config\*/'IConfigAdapter')) {
			throw new /*\*/InvalidArgumentException("Configuration adapter '$class' is not Nette\\Config\\IConfigAdapter implementor.");
		}

		self::$extensions[strtolower($extension)] = $class;
	}



	/**
	 * Creates new configuration object from file.
	 * @param  string  file name
	 * @param  string  section to load
	 * @param  int     flags (readOnly, autoexpand variables)
	 * @return Config
	 */
	public static function fromFile($file, $section = NULL, $flags = self::READONLY)
	{
		$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
		if (isset(self::$extensions[$extension])) {
			$arr = call_user_func(array(self::$extensions[$extension], 'load'), $file, $section);
			return new /**/self/**//*static*/($arr, $flags);

		} else {
			throw new /*\*/InvalidArgumentException("Unknown file extension '$file'.");
		}
	}



	/**
	 * @param  array to wrap
	 * @throws \InvalidArgumentException
	 */
	public function __construct($arr = NULL, $flags = self::READONLY)
	{
		parent::__construct($arr);

		if ($arr !== NULL) {
			if ($flags & self::EXPAND) {
				$this->expand();
			}

			if ($flags & self::READONLY) {
				$this->freeze();
			}
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
		$this->updating();

		$data = $this->getArrayCopy();
		foreach ($data as $key => $val) {
			if (is_string($val)) {
				$data[$key] = /*Nette\*/Environment::expand($val);
			} elseif ($val instanceof self) {
				$val->expand();
			}
		}
		$this->setArray($data);
	}



	/**
	 * Import from array or any traversable object.
	 * @param  array|\Traversable
	 * @return void
	 * @throws \InvalidArgumentException
	 */
	public function import($arr)
	{
		$this->updating();

		foreach ($arr as $key => $val) {
			if (is_array($val)) {
				$arr[$key] = $obj = clone $this;
				$obj->import($val);
			}
		}
		$this->setArray($arr);
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
	 * Makes the object unmodifiable.
	 * @return void
	 */
	public function freeze()
	{
		parent::freeze();
		foreach ($this->getArrayCopy() as $val) {
			if ($val instanceof self) {
				$val->freeze();
			}
		}
	}



	/**
	 * Creates a modifiable clone of the object.
	 * @return void
	 */
	public function __clone()
	{
		parent::__clone();
		$data = $this->getArrayCopy();
		foreach ($data as $key => $val) {
			if ($val instanceof self) {
				$data[$key] = clone $val;
			}
		}
		$this->setArray($data);
	}



	/********************* data access via overloading ****************d*g**/



	/**
	 * Returns item. Do not call directly.
	 * @param  int index
	 * @return mixed
	 */
	public function &__get($key)
	{
		$val = $this->offsetGet($key);
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

}
