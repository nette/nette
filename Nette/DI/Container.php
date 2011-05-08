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

	/** @var array  */
	private $types = array();

	/** @var array circular reference detector */
	private $creating;



	/**
	 * Adds the specified service or service factory to the container.
	 * @param  string
	 * @param  mixed   object, class name or callback
	 * @param  string
	 * @return Container|ServiceBuilder  provides a fluent interface
	 */
	public function addService($name, $service, $typeHint = NULL)
	{
		$this->updating();
		if (!is_string($name) || $name === '') {
			throw new Nette\InvalidArgumentException("Service name must be a non-empty string, " . gettype($name) . " given.");
		}

		$lower = strtolower($name);
		if (isset($this->registry[$lower]) || method_exists($this, "createService$name")) {
			throw new Nette\InvalidStateException("Service '$name' has already been registered.");
		}

		if ($service instanceof self) {
			$this->registry[$lower] = & $service->registry[$lower];
			$this->factories[$lower] = & $service->factories[$lower];
			$this->types[$lower] = !$typeHint && isset($service->types[$lower]) ? $service->types[$lower] : $typeHint;
			return $this;

		} elseif (is_string($service) && strpos($service, ':') === FALSE) { // class name
			$typeHint = $typeHint ?: $service;
			$service = new ServiceBuilder($service);
		}

		if ($service instanceof IServiceBuilder) {
			$factory = array($service, 'createService');

		} elseif (is_object($service) && !$service instanceof \Closure && !$service instanceof Nette\Callback) {
			$this->registry[$lower] = $service;
			$this->types[$lower] = $typeHint;
			return $this;

		} else {
			$factory = $service;
		}

		$this->factories[$lower] = array(callback($factory));
		$this->types[$lower] = $typeHint;
		$this->registry[$lower] = & $this->factories[$lower][1]; // forces cloning using reference
		return $service;
	}



	/**
	 * Removes the specified service type from the container.
	 * @return void
	 */
	public function removeService($name)
	{
		$this->updating();
		$lower = strtolower($name);
		unset($this->registry[$lower], $this->factories[$lower]);
	}



	/**
	 * Gets the service object by name.
	 * @param  string
	 * @return object
	 */
	public function getService($name)
	{
		$lower = strtolower($name);
		if (isset($this->registry[$lower])) {
			return $this->registry[$lower];
		}

		if (isset($this->creating[$lower])) {
			throw new Nette\InvalidStateException("Circular reference detected for services: "
				. implode(', ', array_keys($this->creating)) . ".");
		}

		if (isset($this->factories[$lower])) {
			list($factory) = $this->factories[$lower];
			if (!$factory->isCallable()) {
				throw new Nette\InvalidStateException("Unable to create service '$name', factory '$factory' is not callable.");
			}

			$this->creating[$lower] = TRUE;
			try {
				$service = $factory/*5.2*->invoke*/($this);
			} catch (\Exception $e) {}

		} elseif (method_exists($this, "createService$name")) { // static method
			$this->creating[$lower] = TRUE;
			$factory = 'createService' . ucfirst($name);
			try {
				$service = $this->$factory();
			} catch (\Exception $e) {}

		} else {
			throw new MissingServiceException("Service '$name' not found.");
		}

		unset($this->creating[$lower]);

		if (isset($e)) {
			throw $e;

		} elseif (!is_object($service)) {
			throw new Nette\UnexpectedValueException("Unable to create service '$name', value returned by factory '$factory' is not object.");

		} elseif (isset($this->types[$lower]) && !$service instanceof $this->types[$lower]) {
			throw new Nette\UnexpectedValueException("Unable to create service '$name', value returned by factory '$factory' is not '{$this->types[$lower]}' type.");
		}

		unset($this->factories[$lower]);
		return $this->registry[$lower] = $service;
	}



	/**
	 * Gets the service object of the specified type.
	 * @param  string service name
	 * @return object
	 */
	public function getServiceByType($type)
	{
		foreach ($this->registry as $name => $service) {
			if (isset($this->types[$name]) ? !strcasecmp($this->types[$name], $type) : $service instanceof $type) {
				$found[] = $name;
			}
		}
		if (!isset($found)) {
			throw new MissingServiceException("Service matching '$type' type not found.");

		} elseif (count($found) > 1) {
			throw new AmbiguousServiceException("Found more than one service ('" . implode("', '", $found) . "') matching '$type' type.");
		}
		return $this->getService($found[0]);
	}



	/**
	 * Exists the service?
	 * @param  string service name
	 * @param  bool   must be created?
	 * @return bool
	 */
	public function hasService($name, $created = FALSE)
	{
		$lower = strtolower($name);
		return isset($this->registry[$lower])
			|| (!$created && (isset($this->factories[$lower]) || method_exists($this, "createService$name")));
	}



	/**
	 * Checks the service type.
	 * @param  string
	 * @param  string
	 * @return bool
	 */
	public function checkServiceType($name, $type)
	{
		$lower = strtolower($name);
		return isset($this->types[$lower])
			? !strcasecmp($this->types[$lower], $type)
			: (isset($this->registry[$lower]) && $this->registry[$lower] instanceof $type);
	}



	/********************* parameters ****************d*g**/



	/**
	 * Sets all parameters.
	 * @param  array
	 * @return Container  provides a fluent interface
	 */
	public function setParams(array $params)
	{
		$this->updating();
		$this->params = $params;
		return $this;
	}



	/**
	 * Returns array of all parameters.
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}



	/**
	 * Set parameter.
	 * @return Container  provides a fluent interface
	 */
	public function setParam($key, $value)
	{
		$this->updating();
		$this->params[$key] = $value;
		return $this;
	}



	/**
	 * Gets parameter.
	 * @return mixed
	 */
	public function getParam($key)
	{
		return $this->params[$key];
	}



	/********************* shortcuts ****************d*g**/



	/**
	 * Gets the service object, shortcut for getService().
	 * @param  string
	 * @return object
	 */
	public function &__get($name)
	{
		$service = $this->getService($name);
		return $service;
	}



	/**
	 * Adds the service, shortcut for addService().
	 * @param  string
	 * @param  object
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->addService($name, $value);
	}



	/**
	 * Exists the service?
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
