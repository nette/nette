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
	/** @var array  storage for shared objects */
	private $registry = array();

	/** @var array  storage for service factories */
	private $factories = array();



	/**
	 * Adds the specified service or service factory to the container.
	 * @param  string
	 * @param  mixed  object, class name or callback
	 * @return Container  provides a fluent interface
	 */
	public function addService($name, $service)
	{
		$this->updating();
		if (!is_string($name) || $name === '') {
			throw new Nette\InvalidArgumentException("Service name must be a non-empty string, " . gettype($name) . " given.");
		}

		$lower = strtolower($name);
		if (isset($this->registry[$lower])) {
			throw new AmbiguousServiceException("Service named '$name' has already been registered.");
		}

		if ($service instanceof self) {
			$this->registry[$lower] = & $service->registry[$lower];
			$this->factories[$lower] = & $service->factories[$lower];

		} elseif (is_object($service) && !($service instanceof \Closure || $service instanceof Nette\Callback)) {
			$this->registry[$lower] = $service;

		} else {
			$service = is_string($service) && strpos($service, ':') === FALSE // class name?
				? $service : callback($service);
			$this->factories[$lower] = array($service);
			$this->registry[$lower] = & $this->factories[$lower][1]; // forces cloning using reference
		}
		return $this;
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
	 * Gets the service object of the specified type.
	 * @param  string service name
	 * @return mixed
	 */
	public function getService($name)
	{
		$lower = strtolower($name);
		if (isset($this->registry[$lower])) {
			return $this->registry[$lower];

		} elseif (isset($this->factories[$lower])) {
			list($factory) = $this->factories[$lower];

			if (is_string($factory)) { // class name
				/*5.2* if ($a = strrpos($factory, '\\')) $factory = substr($factory, $a + 1); // fix namespace*/
				if (!class_exists($factory)) {
					throw new AmbiguousServiceException("Cannot instantiate service '$name', class '$factory' not found.");
				}
				$service = new $factory;

			} else { // factory callback
				if (!$factory->isCallable()) {
					throw new Nette\InvalidStateException("Cannot instantiate service '$name', handler '$factory' is not callable.");
				}
				$service = $factory/*5.2*->invoke*/($this);
				if (!is_object($service)) {
					throw new AmbiguousServiceException("Cannot instantiate service '$name', value returned by '$factory' is not object.");
				}
			}

				unset($this->factories[$lower]);
			return $this->registry[$lower] = $service;

		} else {
			throw new Nette\InvalidStateException("Service '$name' not found.");
		}
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
		return isset($this->registry[$lower]) || (!$created && isset($this->factories[$lower]));
	}

}
