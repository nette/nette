<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\DI;

use Nette,
	Nette\Reflection\ClassType;



/**
 * The dependency injection container default implementation.
 *
 * @author     David Grudl
 */
class Container extends Nette\FreezableObject
{
	const TAGS = 'tags';
	const GENERIC = 'generic';

	/** @var array  user parameters */
	/*private*/public $parameters = array();

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
	 * Adds the service or service factory to the container.
	 * @param  string
	 * @param  mixed   object, class name or callable
	 * @param  array   service meta information
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

		} elseif (!is_string($service) || strpos($service, ':') !== FALSE/*5.2* || $service[0] === "\0"*/) { // callable
			$service = new Nette\Callback($service);
		}

		$this->factories[$name] = array($service);
		$this->registry[$name] =& $this->factories[$name][1]; // forces cloning using reference
		$this->meta[$name] = $meta;
		return $this;
	}



	/**
	 * Removes the service from the container.
	 * @param  string
	 * @return void
	 */
	public function removeService($name)
	{
		$this->updating();
		unset($this->registry[$name], $this->factories[$name]);
		if (!$this->isCompiled($name)) {
			unset($this->meta[$name]);
		}
	}



	/**
	 * Gets the service object by name.
	 * @param  string
	 * @param  string
	 * @return object
	 */
	public function getService($serviceName, $genericType = NULL)
	{
		if ($genericType !== NULL) {
			$name = $serviceName . '<' . $genericType . '>';

		} elseif ($generic = Helpers::isGeneric($serviceName)) {
			$name = $serviceName;
			$serviceName = $generic[0];
			$genericType = $generic[1];

		} else {
			$name = $serviceName;
		}

		if (isset($this->registry[$name])) {
			return $this->registry[$name];

		} elseif (isset($this->creating[$name])) {
			throw new Nette\InvalidStateException("Circular reference detected for services: "
				. implode(', ', array_keys($this->creating)) . ".");
		}

		if (isset($this->factories[$serviceName])) {
			if ($genericType !== NULL) {
				throw new Nette\InvalidStateException("Unable to create service '$serviceName<$genericType>', only compiled services can be generic.");
			}

			list($factory) = $this->factories[$serviceName];
			if (is_string($factory)) {
				if (!class_exists($factory)) {
					throw new Nette\InvalidStateException("Cannot instantiate service, class '$factory' not found.");
				}
				try {
					$this->creating[$name] = TRUE;
					$service = new $factory;
				} catch (\Exception $e) {}

			} elseif (!$factory->isCallable()) {
				throw new Nette\InvalidStateException("Unable to create service '$serviceName', factory '$factory' is not callable.");

			} else {
				$this->creating[$name] = TRUE;
				try {
					$service = $factory/*5.2*->invoke*/($this);
				} catch (\Exception $e) {}
			}

		} elseif ($this->isCompiled($serviceName)) {
			if ($genericType !== NULL) {
				if (!$this->isGeneric($serviceName)) {
					throw new Nette\InvalidStateException("Unable to create service '$serviceName<$genericType>', service is not generic.");

				} elseif (!class_exists($genericType)) {
					throw new Nette\InvalidStateException("Unable to create service '$serviceName<$genericType>', class $genericType not found.");
				}
			}

			$this->creating[$name] = TRUE;
			$factory = Container::getMethodName($serviceName);

			try {
				if ($genericType !== NULL) {
					$service = $this->$factory(ClassType::from($genericType)->getName());
				} else {
					$service = $this->$factory();
				}
			} catch (\Exception $e) { }

		} else {
			throw new MissingServiceException("Service '$serviceName' not found.");
		}

		unset($this->creating[$name]);

		if (isset($e)) {
			throw $e;

		} elseif (!is_object($service)) {
			throw new Nette\UnexpectedValueException("Unable to create service '$serviceName', value returned by factory '$factory' is not object.");
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
	 * @param  string
	 * @return bool
	 */
	protected function isCompiled($name)
	{
		return method_exists($this, $factory = Container::getMethodName($name))
			&& $this->getReflection()->getMethod($factory)->getName() === $factory;
	}



	/**
	 * Is the service generic?
	 * @param  string
	 * @return bool
	 */
	protected function isGeneric($name)
	{
		return isset($this->meta[$name][self::GENERIC])
			&& $this->meta[$name][self::GENERIC];
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
		$lower = ltrim(strtolower($class), '\\');
		if (!isset($this->classes[$lower])) {
			if (($generic = Nette\DI\Helpers::isGeneric($lower)) && isset($this->classes[$generic[0]])) {
				$service = $this->getService($this->classes[$generic[0]], $generic[1]);
				$this->classes[$lower] = $this->classes[$generic[0]]; // caching
				return $service;
			}

			if ($need) {
				throw new MissingServiceException("Service of type $class not found.");
			}

		} elseif ($this->classes[$lower] === FALSE) {
			throw new MissingServiceException("Multiple services of type $class found.");

		} else {
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
		$rc = ClassType::from($class);
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
			throw new Nette\InvalidArgumentException("Service must be object, " . gettype($service) . " given.");
		}

		foreach (array_reverse(get_class_methods($service)) as $method) {
			if (substr($method, 0, 6) === 'inject') {
				$this->callMethod(array($service, $method));
			}
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
		$callback = new Nette\Callback($function);
		return $callback->invokeArgs(Helpers::autowireArguments($callback->toReflection(), $args, $this));
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



	public static function getMethodName($name, $isService = TRUE)
	{
		if ($isService && ($generic = Helpers::isGeneric($name))) {
			$name = $generic[0];
		}
		$uname = ucfirst($name);
		return ($isService ? 'createService' : 'create') . ($name === $uname ? '__' : '') . str_replace('.', '__', $uname);
	}

}
