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
	const TAG_TYPEHINT = 'typeHint';

	/** @var array  user parameters */
	public $params = array();

	/** @var array  storage for shared objects */
	private $registry = array();

	/** @var array  storage for service factories */
	private $factories = array();

	/** @var array  */
	private $tags = array();

	/** @var array circular reference detector */
	private $creating;



	/**
	 * Adds the specified service or service factory to the container.
	 * @param  string
	 * @param  mixed   object, class name or callback
	 * @param  mixed   array of tags or string typeHint
	 * @return Container|ServiceBuilder  provides a fluent interface
	 */
	public function addService($name, $service, $tags = NULL)
	{
		$this->updating();
		if (!is_string($name) || $name === '') {
			throw new Nette\InvalidArgumentException("Service name must be a non-empty string, " . gettype($name) . " given.");
		}

		if (isset($this->registry[$name]) || method_exists($this, "createService$name")) {
			throw new Nette\InvalidStateException("Service '$name' has already been registered.");
		}

		if (is_string($tags)) {
			$tags = array(self::TAG_TYPEHINT => array($tags));
		} elseif (is_array($tags)) {
			foreach ($tags as $id => $attrs) {
				if (is_int($id) && is_string($attrs)) {
					$tags[$attrs] = array();
					unset($tags[$id]);
				} elseif (!is_array($attrs)) {
					$tags[$id] = (array) $attrs;
				}
			}
		}

		if (is_string($service) && strpos($service, ':') === FALSE/*5.2* && $service[0] !== "\0"*/) { // class name
			if (!isset($tags[self::TAG_TYPEHINT][0])) {
				$tags[self::TAG_TYPEHINT][0] = $service;
			}
			$service = new ServiceBuilder($service);
		}

		if ($service instanceof IServiceBuilder) {
			$factory = array($service, 'createService');

		} elseif (is_object($service) && !$service instanceof \Closure && !$service instanceof Nette\Callback) {
			$this->registry[$name] = $service;
			$this->tags[$name] = $tags;
			return $this;

		} else {
			$factory = $service;
		}

		$this->factories[$name] = array(callback($factory));
		$this->tags[$name] = $tags;
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

		} elseif (isset($this->tags[$name][self::TAG_TYPEHINT][0]) && !$service instanceof $this->tags[$name][self::TAG_TYPEHINT][0]) {
			throw new Nette\UnexpectedValueException("Unable to create service '$name', value returned by factory '$factory' is not '{$this->tags[$name][self::TAG_TYPEHINT][0]}' type.");
		}

		unset($this->factories[$name]);
		return $this->registry[$name] = $service;
	}



	/**
	 * Gets the service object of the specified type.
	 * @param  string
	 * @return object
	 */
	public function getServiceByType($type)
	{
		foreach ($this->registry as $name => $service) {
			if (isset($this->tags[$name][self::TAG_TYPEHINT][0]) ? !strcasecmp($this->tags[$name][self::TAG_TYPEHINT][0], $type) : $service instanceof $type) {
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
	 * Gets the service objects of the specified tag.
	 * @param  string
	 * @return array of [service name => tag attributes]
	 */
	public function getServiceNamesByTag($tag)
	{
		$found = array();
		foreach ($this->registry as $name => $service) {
			if (isset($this->tags[$name][$tag])) {
				$found[$name] = $this->tags[$name][$tag];
			}
		}
		return $found;
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
	 * Checks the service type.
	 * @param  string
	 * @param  string
	 * @return bool
	 */
	public function checkServiceType($name, $type)
	{
		return isset($this->tags[$name][self::TAG_TYPEHINT][0])
			? !strcasecmp($this->tags[$name][self::TAG_TYPEHINT][0], $type)
			: (isset($this->registry[$name]) && $this->registry[$name] instanceof $type);
	}



	/********************* tools ****************d*g**/



	/**
	 * Expands %placeholders% in string.
	 * @param  mixed
	 * @return mixed
	 * @throws Nette\InvalidStateException
	 */
	public function expand($s)
	{
		if (!is_string($s) || strpos($s, '%') === FALSE) {
			return $s;
		}

		$parts = preg_split('#%([a-z0-9._-]*)%#i', $s, -1, PREG_SPLIT_DELIM_CAPTURE);
		if (strlen($s) === strlen($parts[1]) + 2) {
			return Nette\Utils\Arrays::get($this->params, explode('.', $parts[1]));
		}
		$res = '';
		foreach ($parts as $n => $part) {
			if ($n % 2 === 0) {
				$res .= $part;

			} elseif ($part === '') {
				$res .= '%';

			} else {
				$val = Nette\Utils\Arrays::get($this->params, explode('.', $part));
				if (!is_scalar($val)) {
					throw new Nette\InvalidStateException("Unable to concatenate non-scalar parameter '$part' into '$s'.");
				}
				$res .= $val;
			}
		}
		return $res;
	}



	/********************* shortcuts ****************d*g**/



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
