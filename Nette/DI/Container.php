<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\DI;

use Nette;


/**
 * The dependency injection container default implementation.
 *
 * @author     David Grudl
 */
class Container extends Nette\Object
{
	const TAGS = 'tags';
	const TYPES = 'types';

	/** @var array  user parameters */
	/*private*/public $parameters = array();

	/** @var array  storage for shared objects */
	private $registry = array();

	/** @var array[] */
	protected $meta = array();

	/** @var array circular reference detector */
	private $creating;


	public function __construct(array $params = array())
	{
		$this->parameters = $params + $this->parameters;
	}


	/**
	 * @return array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}


	/**
	 * Adds the service to the container.
	 * @param  string
	 * @param  object
	 * @return self
	 */
	public function addService($name, $service)
	{
		if (func_num_args() > 2) {
			throw new Nette\DeprecatedException('Parameter $meta has been removed.');

		} elseif (!is_string($name) || !$name) {
			throw new Nette\InvalidArgumentException(sprintf('Service name must be a non-empty string, %s given.', gettype($name)));

		} elseif (isset($this->registry[$name])) {
			throw new Nette\InvalidStateException("Service '$name' already exists.");

		} elseif (is_string($service) || is_array($service) || $service instanceof \Closure || $service instanceof Nette\Callback) {
			trigger_error(sprintf('Passing factories to %s() is deprecated; pass the object itself.', __METHOD__), E_USER_DEPRECATED);
			$service = is_string($service) && !preg_match('#\x00|:#', $service) ? new $service : call_user_func($service, $this);
		}

		if (!is_object($service)) {
			throw new Nette\InvalidArgumentException(sprintf('Service must be a object, %s given.', gettype($service)));
		}

		$this->registry[$name] = $service;
		return $this;
	}


	/**
	 * Removes the service from the container.
	 * @param  string
	 * @return void
	 */
	public function removeService($name)
	{
		unset($this->registry[$name]);
	}


	/**
	 * Gets the service object by name.
	 * @param  string
	 * @return object
	 * @throws MissingServiceException
	 */
	public function getService($name)
	{
		if (!isset($this->registry[$name])) {
			$this->registry[$name] = $this->createService($name);
		}
		return $this->registry[$name];
	}


	/**
	 * Does the service exist?
	 * @param  string service name
	 * @return bool
	 */
	public function hasService($name)
	{
		return isset($this->registry[$name])
			|| method_exists($this, $method = Container::getMethodName($name)) && $this->getReflection()->getMethod($method)->getName() === $method;
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
	 * Creates new instance of the service.
	 * @param  string service name
	 * @return object
	 * @throws MissingServiceException
	 */
	public function createService($name, array $args = array())
	{
		$method = Container::getMethodName($name);
		if (isset($this->creating[$name])) {
			throw new Nette\InvalidStateException(sprintf('Circular reference detected for services: %s.', implode(', ', array_keys($this->creating))));

		} elseif (!method_exists($this, $method) || $this->getReflection()->getMethod($method)->getName() !== $method) {
			throw new MissingServiceException("Service '$name' not found.");
		}

		$this->creating[$name] = TRUE;
		try {
			$service = call_user_func_array(array($this, $method), $args);
		} catch (\Exception $e) {
			unset($this->creating[$name]);
			throw $e;
		}
		unset($this->creating[$name]);

		if (!is_object($service)) {
			throw new Nette\UnexpectedValueException("Unable to create service '$name', value returned by method $method() is not object.");
		}

		return $service;
	}


	/**
	 * Resolves service by type.
	 * @param  string  class or interface
	 * @param  bool    throw exception if service doesn't exist?
	 * @return object  service or NULL
	 * @throws MissingServiceException
	 */
	public function getByType($class, $need = TRUE)
	{
		$names = $this->findByType($class);
		if (!$names) {
			if ($need) {
				throw new MissingServiceException("Service of type $class not found.");
			}
		} elseif (count($names) > 1) {
			throw new MissingServiceException("Multiple services of type $class found: " . implode(', ', $names) . '.');
		} else {
			return $this->getService($names[0]);
		}
	}


	/**
	 * Gets the service names of the specified type.
	 * @param  string
	 * @return string[]
	 */
	public function findByType($class)
	{
		$class = ltrim(strtolower($class), '\\');
		return isset($this->meta[self::TYPES][$class]) ? $this->meta[self::TYPES][$class] : array();
	}


	/**
	 * Gets the service names of the specified tag.
	 * @param  string
	 * @return array of [service name => tag attributes]
	 */
	public function findByTag($tag)
	{
		return isset($this->meta[self::TAGS][$tag]) ? $this->meta[self::TAGS][$tag] : array();
	}


	/********************* autowiring ****************d*g**/


	/**
	 * Creates new instance using autowiring.
	 * @param  string  class
	 * @param  array   arguments
	 * @return object
	 * @throws Nette\InvalidArgumentException
	 */
	public function createInstance($class, array $args = array())
	{
		$rc = Nette\Reflection\ClassType::from($class);
		if (!$rc->isInstantiable()) {
			throw new ServiceCreationException("Class $class is not instantiable.");

		} elseif ($constructor = $rc->getConstructor()) {
			return $rc->newInstanceArgs(Helpers::autowireArguments($constructor, $args, $this));

		} elseif ($args) {
			throw new ServiceCreationException("Unable to pass arguments, class $class has no constructor.");
		}
		return new $class;
	}


	/**
	 * Calls all methods starting with with "inject" using autowiring.
	 * @param  object
	 * @return void
	 */
	public function callInjects($service)
	{
		if (!is_object($service)) {
			throw new Nette\InvalidArgumentException(sprintf('Service must be object, %s given.', gettype($service)));
		}

		foreach (array_reverse(get_class_methods($service)) as $method) {
			if (substr($method, 0, 6) === 'inject') {
				$this->callMethod(array($service, $method));
			}
		}

		foreach (Helpers::getInjectProperties(Nette\Reflection\ClassType::from($service), $this) as $property => $type) {
			$service->$property = $this->getByType($type);
		}
	}


	/**
	 * Calls method using autowiring.
	 * @param  mixed   class, object, function, callable
	 * @param  array   arguments
	 * @return mixed
	 */
	public function callMethod($function, array $args = array())
	{
		return call_user_func_array(
			$function,
			Helpers::autowireArguments(Nette\Utils\Callback::toReflection($function), $args, $this)
		);
	}


	/********************* shortcuts ****************d*g**/


	/**
	 * Expands %placeholders%.
	 * @param  mixed
	 * @return mixed
	 */
	public function expand($s)
	{
		return Helpers::expand($s, $this->parameters);
	}


	/** @deprecated */
	public function &__get($name)
	{
		$this->error(__METHOD__, 'getService');
		$tmp = $this->getService($name);
		return $tmp;
	}


	/** @deprecated */
	public function __set($name, $service)
	{
		$this->error(__METHOD__, 'addService');
		$this->addService($name, $service);
	}


	/** @deprecated */
	public function __isset($name)
	{
		$this->error(__METHOD__, 'hasService');
		return $this->hasService($name);
	}


	/** @deprecated */
	public function __unset($name)
	{
		$this->error(__METHOD__, 'removeService');
		$this->removeService($name);
	}


	private function error($oldName, $newName)
	{
		if (empty($this->parameters['container']['accessors'])) {
			trigger_error("$oldName() is deprecated; use $newName() or enable nette.container.accessors in configuration.", E_USER_DEPRECATED);
		}
	}


	public static function getMethodName($name)
	{
		$uname = ucfirst($name);
		return 'createService' . ((string) $name === $uname ? '__' : '') . str_replace('.', '__', $uname);
	}

}
