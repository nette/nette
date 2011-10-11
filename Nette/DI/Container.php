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
	/** @var array  user parameters */
	public $params = array();

	/** @var array  storage for shared objects */
	private $registry = array();

	/** @var array  storage for service factories */
	private $factories = array();

	/** @var array circular reference detector */
	private $creating;



	/**
	 * Adds the specified service or service factory to the container.
	 * @param  string
	 * @param  mixed   object, class name or callback
	 * @return Container|ServiceBuilder  provides a fluent interface
	 */
	public function addService($name, $service)
	{
		$this->updating();
		if (!is_string($name) || $name === '') {
			throw new Nette\InvalidArgumentException("Service name must be a non-empty string, " . gettype($name) . " given.");
		}

		if (isset($this->registry[$name]) || method_exists($this, "createService$name")) {
			throw new Nette\InvalidStateException("Service '$name' has already been registered.");
		}

		if (is_string($service) && strpos($service, ':') === FALSE/*5.2* && $service[0] !== "\0"*/) { // class name
			$service = new ServiceBuilder($service);
		}

		if ($service instanceof IServiceBuilder) {
			$factory = array($service, 'createService');

		} elseif (is_object($service) && !$service instanceof \Closure && !$service instanceof Nette\Callback) {
			$this->registry[$name] = $service;
			return $this;

		} else {
			$factory = $service;
		}

		$this->factories[$name] = array(callback($factory));
		$this->registry[$name] = & $this->factories[$name][1]; // forces cloning using reference
		return $service;
	}



	/**
	 * Removes the specified service type from the container.
	 * @return void
	 */
	public function removeService($name)
	{
		$this->updating();
		unset($this->registry[$name], $this->factories[$name]);
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
			if (!$factory->isCallable()) {
				throw new Nette\InvalidStateException("Unable to create service '$name', factory '$factory' is not callable.");
			}

			$this->creating[$name] = TRUE;
			try {
				$service = $factory/*5.2*->invoke*/($this);
			} catch (\Exception $e) {}

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

		unset($this->factories[$name]);
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



	/********************* shortcuts ****************d*g**/



	/**
	 * Expands %placeholders% in string.
	 * @param  mixed
	 * @return mixed
	 */
	public function expand($s)
	{
		return is_string($s) ? Nette\Utils\Strings::expand($s, $this->params) : $s;
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

		} elseif (isset($this->registry[$name]) || method_exists($this, "createService$name")) {
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
