<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\DI;

use Nette;



/**
 * The dependency injection container default implementation.
 *
 * @author     David Grudl
 */
class Container extends Nette\FreezableObject implements IContainer
{
	const TAGS = 'tags';

	/** @var array  user parameters */
	public $parameters = array();

	/** @deprecated */
	public $params = array();

	/** @var array */
	public $classes = array();

	/** @var array  storage for shared objects */
	private $registry = array();

	/** @var array  storage for service factories */
	private $factories = array();

	/** @var array */
	public $meta = array();

	/** @var array circular reference detector */
	private $creating;



	public function __construct()
	{
		$this->params = &$this->parameters;
	}



	/**
	 * Adds the specified service or service factory to the container.
	 * @param  string
	 * @param  mixed   object, class name or callback
	 * @return Container  provides a fluent interface
	 */
	public function addService($name, $service, array $meta = NULL)
	{
		$this->updating();
		if (!is_string($name) || $name === '') {
			throw new Nette\InvalidArgumentException("Service name must be a non-empty string, " . gettype($name) . " given.");
		}

		if (isset($this->registry[$name])) {
			throw new Nette\InvalidStateException("Service '$name' has already been registered.");
		}

		if (is_object($service) && !$service instanceof \Closure && !$service instanceof Nette\Callback) {
			$this->registry[$name] = $service;
			$this->meta[$name] = $meta;
			return $this;

		} elseif (!is_string($service) || strpos($service, ':') !== FALSE/*5.2* || $service[0] === "\0"*/) { // callback
			$service = callback($service);
		}

		$this->factories[$name] = array($service);
		$this->registry[$name] = & $this->factories[$name][1]; // forces cloning using reference
		$this->meta[$name] = $meta;
		return $this;
	}



	/**
	 * Removes the specified service type from the container.
	 * @return void
	 */
	public function removeService($name)
	{
		$this->updating();
		unset($this->registry[$name], $this->factories[$name], $this->meta[$name]);
	}



	/**
	 * Gets the service object by name.
	 * @param  string
	 * @return object
	 */
	public function getService($name)
	{
		if (isset($this->registry[$name])) {
			return $this->registry[$name];

		} elseif (isset($this->creating[$name])) {
			throw new Nette\InvalidStateException("Circular reference detected for services: "
				. implode(', ', array_keys($this->creating)) . ".");
		}

		if (isset($this->factories[$name])) {
			list($factory) = $this->factories[$name];
			if (is_string($factory)) {
				if (!class_exists($factory)) {
					throw new Nette\InvalidStateException("Cannot instantiate service, class '$factory' not found.");
				}
				try {
					$this->creating[$name] = TRUE;
					$service = new $factory;
				} catch (\Exception $e) {}

			} elseif (!$factory->isCallable()) {
				throw new Nette\InvalidStateException("Unable to create service '$name', factory '$factory' is not callable.");

			} else {
				$this->creating[$name] = TRUE;
				try {
					$service = $factory/*5.2*->invoke*/($this);
				} catch (\Exception $e) {}
			}

		} elseif (method_exists($this, $factory = 'createService' . ucfirst($name))) { // static method
			$this->creating[$name] = TRUE;
			try {
				$service = $this->$factory();
			} catch (\Exception $e) {}

		} else {
			throw new MissingServiceException("Service '$name' not found.");
		}

		unset($this->creating[$name]);

		if (isset($e)) {
			throw $e;

		} elseif (!is_object($service)) {
			throw new Nette\UnexpectedValueException("Unable to create service '$name', value returned by factory '$factory' is not object.");
		}

		return $this->registry[$name] = $service;
	}



	/**
	 * Does the service exist?
	 * @param  string service name
	 * @return bool
	 */
	public function hasService($name)
	{
		return isset($this->registry[$name])
			|| isset($this->factories[$name])
			|| method_exists($this, "createService$name");
	}



	/**
	 * Is the service created?
	 * @param  string service name
	 * @return bool
	 */
	public function isCreated($name)
	{
		if (!$this->hasService($name)) {
			throw new MissingServiceException("Service '$name' not found.");
		}
		return isset($this->registry[$name]);
	}



	/**
	 * Resolves service by type.
	 * @param  string  class or interface
	 * @return object  service or NULL
	 * @throws ServiceCreationException
	 */
	public function findByClass($class)
	{
		$lower = ltrim(strtolower($class), '\\');
		if (isset($this->classes[$lower])) {
			if ($this->classes[$lower] === FALSE) {
				throw new ServiceCreationException("Multiple services of type $class found.");
			}
			return $this->getService($this->classes[$lower]);
		}
	}



	/**
	 * Gets the service names of the specified tag.
	 * @param  string
	 * @return array of [service name => tag attributes]
	 */
	public function findByTag($tag)
	{
		$found = array();
		foreach ($this->meta as $name => $meta) {
			if (isset($meta[self::TAGS][$tag])) {
				$found[$name] = $meta[self::TAGS][$tag];
			}
		}
		return $found;
	}



	/********************* shortcuts ****************d*g**/



	/**
	 * Expands %placeholders% in string.
	 * @param  mixed
	 * @return mixed
	 */
	public function expand($s)
	{
		return is_string($s) ? Nette\Utils\Strings::expand($s, $this->parameters) : $s;
	}



	/**
	 * Gets the service object, shortcut for getService().
	 * @param  string
	 * @return object
	 */
	public function &__get($name)
	{
		if (!isset($this->registry[$name])) {
			$this->getService($name);
		}
		return $this->registry[$name];
	}



	/**
	 * Adds the service object.
	 * @param  string
	 * @param  object
	 * @return void
	 */
	public function __set($name, $service)
	{
		$this->updating();
		if (!is_string($name) || $name === '') {
			throw new Nette\InvalidArgumentException("Service name must be a non-empty string, " . gettype($name) . " given.");

		} elseif (isset($this->registry[$name])) {
			throw new Nette\InvalidStateException("Service '$name' has already been registered.");

		} elseif (!is_object($service)) {
			throw new Nette\InvalidArgumentException("Service must be a object, " . gettype($service) . " given.");
		}
		$this->registry[$name] = $service;
	}



	/**
	 * Does the service exist?
	 * @param  string
	 * @return bool
	 */
	public function __isset($name)
	{
		return $this->hasService($name);
	}



	/**
	 * Removes the service, shortcut for removeService().
	 * @return void
	 */
	public function __unset($name)
	{
		$this->removeService($name);
	}

}
