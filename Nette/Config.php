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
 * @package    Nette
 * @version    $Id$
 */

/*namespace Nette;*/



require_once dirname(__FILE__) . '/Collections/Hashtable.php';



/**
 * Configuration storage.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette
 */
class Config extends /*Nette::Collections::*/Hashtable
{
	/** flags: */
	const READONLY = 1;
	const EXPAND = 2;



	/********************* I/O operations ****************d*g**/


	/**
	 * @param  array to wrap
	 * @throws ::InvalidArgumentException
	 */
	public function __construct($arr = NULL, $flags = self::READONLY)
	{
		parent::__construct($arr);

		if ($arr !== NULL) {
			if ($flags & self::EXPAND) {
				$this->expand();
			}

			if ($flags & self::READONLY) {
				$this->setReadOnly();
			}
		}
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
		$class = /*Nette::*/'ConfigAdapter_' . strtoupper(pathinfo($file, PATHINFO_EXTENSION));
		if (class_exists($class)) {
			$arr = call_user_func(array($class, 'load'), $file, $section);
			return new /**/self/**//*static*/($arr, $flags);

		} else {
			throw new /*::*/InvalidArgumentException("Unknown file extension '$file'.");
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
		$class = /*Nette::*/'ConfigAdapter_' . strtoupper(pathinfo($file, PATHINFO_EXTENSION));
		if (class_exists($class)) {
			return call_user_func(array($class, 'save'), $this, $file, $section);

		} else {
			throw new /*::*/InvalidArgumentException("Unknown file extension '$file'.");
		}
	}



	/********************* data access ****************d*g**/



	/**
	 * Expand all variables.
	 * @return void
	 */
	public function expand()
	{
		if ($this->readOnly) {
			throw new /*::*/NotSupportedException('Configuration is read-only.');
		}

		$data = $this->getArrayCopy();
		foreach ($data as $key => $val) {
			if (is_string($val)) {
				$data[$key] = Environment::expand($val);
			} elseif ($val instanceof self) {
				$val->expand();
			}
		}
		$this->setArray($data);
	}



	/**
	 * Import from array or any traversable object.
	 * @param  array|Traversable
	 * @return void
	 * @throws ::InvalidArgumentException
	 */
	public function import($arr)
	{
		if ($this->readOnly) {
			throw new /*::*/NotSupportedException('Configuration is read-only.');
		}

		foreach ($arr as $key => $val) {
			if (is_array($val)) {
				$arr[$key] = $obj = clone $this;
				$obj->readOnly = & $this->readOnly;
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



	/********************* overloading ****************d*g**/



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
