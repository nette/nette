<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Http;

use Nette;



/**
 * Session namespace for Session.
 *
 * @author     David Grudl
 */
final class SessionNamespace extends Nette\Object implements \IteratorAggregate, \ArrayAccess
{
	/** @var array  session data storage */
	private $data;

	/** @var array  session metadata storage */
	private $meta;

	/** @var bool */
	public $warnOnUndefined = FALSE;



	/**
	 * Do not call directly. Use Session::getNamespace().
	 */
	public function __construct(& $data, & $meta)
	{
		$this->data = & $data;
		$this->meta = & $meta;
	}



	/**
	 * Returns an iterator over all namespace variables.
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		if (isset($this->data)) {
			return new \ArrayIterator($this->data);
		} else {
			return new \ArrayIterator;
		}
	}



	/**
	 * Sets a variable in this session namespace.
	 * @param  string  name
	 * @param  mixed   value
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->data[$name] = $value;
		if (is_object($value)) {
			$this->meta[$name]['V'] = Nette\Reflection\ClassType::from($value)->getAnnotation('serializationVersion');
		}
	}



	/**
	 * Gets a variable from this session namespace.
	 * @param  string    name
	 * @return mixed
	 */
	public function &__get($name)
	{
		if ($this->warnOnUndefined && !array_key_exists($name, $this->data)) {
			trigger_error("The variable '$name' does not exist in session namespace", E_USER_NOTICE);
		}

		return $this->data[$name];
	}



	/**
	 * Determines whether a variable in this session namespace is set.
	 * @param  string    name
	 * @return bool
	 */
	public function __isset($name)
	{
		return isset($this->data[$name]);
	}



	/**
	 * Unsets a variable in this session namespace.
	 * @param  string    name
	 * @return void
	 */
	public function __unset($name)
	{
		unset($this->data[$name], $this->meta[$name]);
	}



	/**
	 * Sets a variable in this session namespace.
	 * @param  string  name
	 * @param  mixed   value
	 * @return void
	 */
	public function offsetSet($name, $value)
	{
		$this->__set($name, $value);
	}



	/**
	 * Gets a variable from this session namespace.
	 * @param  string    name
	 * @return mixed
	 */
	public function offsetGet($name)
	{
		return $this->__get($name);
	}



	/**
	 * Determines whether a variable in this session namespace is set.
	 * @param  string    name
	 * @return bool
	 */
	public function offsetExists($name)
	{
		return $this->__isset($name);
	}



	/**
	 * Unsets a variable in this session namespace.
	 * @param  string    name
	 * @return void
	 */
	public function offsetUnset($name)
	{
		$this->__unset($name);
	}



	/**
	 * Sets the expiration of the namespace or specific variables.
	 * @param  string|int|DateTime  time, value 0 means "until the browser is closed"
	 * @param  mixed   optional list of variables / single variable to expire
	 * @return SessionNamespace  provides a fluent interface
	 */
	public function setExpiration($time, $variables = NULL)
	{
		if (empty($time)) {
			$time = NULL;
			$whenBrowserIsClosed = TRUE;
		} else {
			$time = Nette\DateTime::from($time)->format('U');
			$whenBrowserIsClosed = FALSE;
		}

		if ($variables === NULL) { // to entire namespace
			$this->meta['']['T'] = $time;
			$this->meta['']['B'] = $whenBrowserIsClosed;

		} elseif (is_array($variables)) { // to variables
			foreach ($variables as $variable) {
				$this->meta[$variable]['T'] = $time;
				$this->meta[$variable]['B'] = $whenBrowserIsClosed;
			}

		} else { // to variable
			$this->meta[$variables]['T'] = $time;
			$this->meta[$variables]['B'] = $whenBrowserIsClosed;
		}
		return $this;
	}



	/**
	 * Removes the expiration from the namespace or specific variables.
	 * @param  mixed   optional list of variables / single variable to expire
	 * @return void
	 */
	public function removeExpiration($variables = NULL)
	{
		if ($variables === NULL) {
			// from entire namespace
			unset($this->meta['']['T'], $this->meta['']['B']);

		} elseif (is_array($variables)) {
			// from variables
			foreach ($variables as $variable) {
				unset($this->meta[$variable]['T'], $this->meta[$variable]['B']);
			}
		} else {
			unset($this->meta[$variables]['T'], $this->meta[$variable]['B']);
		}
	}



	/**
	 * Cancels the current session namespace.
	 * @return void
	 */
	public function remove()
	{
		$this->data = NULL;
		$this->meta = NULL;
	}

}
