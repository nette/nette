<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\DI;

use Nette,
	Nette\Utils\Validators,
	Nette\Utils\Strings,
	Nette\Reflection,
	Nette\PhpGenerator\Helpers as PhpHelpers;


/**
 * Basic container builder.
 *
 * @author     David Grudl
 * @property-read ServiceDefinition[] $definitions
 * @property-read array $dependencies
 */
class ContainerBuilder extends Nette\Object
{
	const THIS_SERVICE = 'self',
		THIS_CONTAINER = 'container';

	/** @var array */
	public $parameters = array();

	/** @var ServiceDefinition[] */
	private $definitions = array();

	/** @var array for auto-wiring */
	private $classes;

	/** @var array of file names */
	private $dependencies = array();

	/** @var Nette\PhpGenerator\ClassType[] */
	private $generatedClasses = array();

	/** @var string */
	/*private in 5.4*/public $currentService;


	/**
	 * Adds new service definition.
	 * @param  string
	 * @return ServiceDefinition
	 */
	public function addDefinition($name, ServiceDefinition $definition = NULL)
	{
		if (!is_string($name) || !$name) { // builder is not ready for falsy names such as '0'
			throw new Nette\InvalidArgumentException("Service name must be a non-empty string, " . gettype($name) . " given.");

		} elseif (isset($this->definitions[$name])) {
			throw new Nette\InvalidStateException("Service '$name' has already been added.");
		}
		return $this->definitions[$name] = $definition ?: new ServiceDefinition;
	}


	/**
	 * Removes the specified service definition.
	 * @param  string
	 * @return void
	 */
	public function removeDefinition($name)
	{
		unset($this->definitions[$name]);
	}


	/**
	 * Gets the service definition.
	 * @param  string
	 * @return ServiceDefinition
	 */
	public function getDefinition($name)
	{
		if (!isset($this->definitions[$name])) {
			throw new MissingServiceException("Service '$name' not found.");
		}
		return $this->definitions[$name];
	}


	/**
	 * Gets all service definitions.
	 * @return array
	 */
	public function getDefinitions()
	{
		return $this->definitions;
	}


	/**
	 * Does the service definition exist?
	 * @param  string
	 * @return bool
	 */
	public function hasDefinition($name)
	{
		return isset($this->definitions[$name]);
	}


	/********************* class resolving ****************d*g**/


	/**
	 * Resolves service name by type.
	 * @param  string  class or interface
	 * @return string  service name or NULL
	 * @throws ServiceCreationException
	 */
	public function getByType($class)
	{
		if ($this->currentService !== NULL && Reflection\ClassType::from($this->definitions[$this->currentService]->class)->is($class)) {
			return $this->currentService;
		}

		$lower = ltrim(strtolower($class), '\\');
		if (!isset($this->classes[$lower])) {
			return;

		} elseif (count($this->classes[$lower]) === 1) {
			return $this->classes[$lower][0];

		} else {
			throw new ServiceCreationException("Multiple services of type $class found: " . implode(', ', $this->classes[$lower]));
		}
	}


	/**
	 * Gets the service objects of the specified tag.
	 * @param  string
	 * @return array of [service name => tag attributes]
	 */
	public function findByTag($tag)
	{
		$found = array();
		foreach ($this->definitions as $name => $def) {
			if (isset($def->tags[$tag])) {
				$found[$name] = $def->tags[$tag];
			}
		}
		return $found;
	}


	/**
	 * Creates a list of arguments using autowiring.
	 * @return array
	 */
	public function autowireArguments($class, $method, array $arguments)
	{
		$rc = Reflection\ClassType::from($class);
		if (!$rc->hasMethod($method)) {
			if (!Nette\Utils\Arrays::isList($arguments)) {
				throw new ServiceCreationException("Unable to pass specified arguments to $class::$method().");
			}
			return $arguments;
		}

		$rm = $rc->getMethod($method);
		if (!$rm->isPublic()) {
			throw new ServiceCreationException("$rm is not callable.");
		}
		$this->addDependency($rm->getFileName());
		return Helpers::autowireArguments($rm, $arguments, $this);
	}


	/**
	 * Generates $dependencies, $classes and normalizes class names.
	 * @return array
	 */
	public function prepareClassList()
	{
		$this->classes = FALSE;

		// prepare generated factories
		foreach ($this->definitions as $name => $def) {
			if (!$def->implement) {
				continue;
			}

			if (!interface_exists($def->implement)) {
				throw new ServiceCreationException("Interface $def->implement has not been found.");
			}
			$rc = Reflection\ClassType::from($def->implement);
			$method = $rc->hasMethod('create') ? $rc->getMethod('create') : ($rc->hasMethod('get') ? $rc->getMethod('get') : NULL);
			if (count($rc->getMethods()) !== 1 || !$method || $method->isStatic()) {
				throw new ServiceCreationException("Interface $def->implement must have just one non-static method create() or get().");
			}
			$def->implement = $rc->getName();
			$def->implementType = $rc->hasMethod('create') ? 'create' : 'get';

			if (!$def->class && empty($def->factory->entity)) {
				$returnType = $method->getAnnotation('return');
				if (!$returnType) {
					throw new ServiceCreationException("Method $method has not @return annotation.");
				}

				$returnType = Reflection\AnnotationsParser::expandClassName(preg_replace('#[|\s].*#', '', $returnType), $rc);
				if (!class_exists($returnType)) {
					throw new ServiceCreationException("Please check a @return annotation of the $method method. Class '$returnType' cannot be found.");
				}
				$def->setClass($returnType);
			}

			if ($method->getName() === 'get') {
				if ($method->getParameters()) {
					throw new ServiceCreationException("Method $method must have no arguments.");
				}
				if (empty($def->factory->entity)) {
					$def->setFactory('@\\' . ltrim($def->class, '\\'));
				} elseif (!$this->getServiceName($def->factory->entity)) {
					throw new ServiceCreationException("Invalid factory in service '$name' definition.");
				}
			}

			if (!$def->parameters) {
				foreach ($method->getParameters() as $param) {
					$paramDef = ($param->isArray() ? 'array' : $param->getClassName()) . ' ' . $param->getName();
					if ($param->isOptional()) {
						$def->parameters[$paramDef] = $param->getDefaultValue();
					} else {
						$def->parameters[] = $paramDef;
					}
				}
			}
		}

		// complete class-factory pairs
		foreach ($this->definitions as $name => $def) {
			if (!$def->factory) {
				if (!$def->class) {
					throw new ServiceCreationException("Class and factory are missing in service '$name' definition.");
				}
				$def->factory = new Statement($def->class);
			}
		}

		// check if services are instantiable
		foreach ($this->definitions as $name => $def) {
			$factory = $def->factory->entity = $this->normalizeEntity($def->factory->entity);

			if (is_string($factory) && preg_match('#^[\w\\\\]+\z#', $factory) && $factory !== self::THIS_SERVICE) {
				if (!class_exists($factory) || !Reflection\ClassType::from($factory)->isInstantiable()) {
					throw new ServiceCreationException("Class $factory used in service '$name' has not been found or is not instantiable.");
				}
			}
		}

		// complete classes
		foreach ($this->definitions as $name => $def) {
			$this->resolveClass($name);

			if (!$def->class) {
				continue;
			} elseif (!class_exists($def->class) && !interface_exists($def->class)) {
				throw new ServiceCreationException("Class or interface $def->class used in service '$name' has not been found.");
			} else {
				$def->class = Reflection\ClassType::from($def->class)->getName();
			}
		}

		//  build auto-wiring list
		$this->classes = array();
		foreach ($this->definitions as $name => $def) {
			$class = $def->implement ?: $def->class;
			if ($def->autowired && $class) {
				foreach (class_parents($class) + class_implements($class) + array($class) as $parent) {
					$this->classes[strtolower($parent)][] = (string) $name;
				}
			}
		}

		foreach ($this->classes as $class => $foo) {
			$this->addDependency(Reflection\ClassType::from($class)->getFileName());
		}
	}


	private function resolveClass($name, $recursive = array())
	{
		if (isset($recursive[$name])) {
			throw new ServiceCreationException('Circular reference detected for services: ' . implode(', ', array_keys($recursive)) . '.');
		}
		$recursive[$name] = TRUE;

		$def = $this->definitions[$name];
		$factory = $def->factory->entity;

		if ($def->class) {
			return $def->class;

		} elseif (is_array($factory)) { // method calling
			if ($service = $this->getServiceName($factory[0])) {
				if (Strings::contains($service, '\\')) { // @\Class
					$factory[0] = $service;
				} else {
					$factory[0] = $this->resolveClass($service, $recursive);
					if (!$factory[0]) {
						return;
					}
					if ($this->definitions[$service]->implement && $factory[1] === 'create') {
						return $def->class = $factory[0];
					}
				}
			}
			if (!is_callable($factory)) {
				throw new ServiceCreationException("Factory '" . Nette\Utils\Callback::toString($factory) . "' is not callable.");
			}
			try {
				$reflection = Nette\Utils\Callback::toReflection($factory);
			} catch (\ReflectionException $e) {
				throw new ServiceCreationException("Missing factory '" . Nette\Utils\Callback::toString($factory) . "'.");
			}
			$def->class = preg_replace('#[|\s].*#', '', $reflection->getAnnotation('return'));
			if ($def->class && $reflection instanceof \ReflectionMethod) {
				$def->class = Reflection\AnnotationsParser::expandClassName($tmp = $def->class, $reflection->getDeclaringClass());
				if ($tmp !== $def->class && $tmp[0] !== '\\' && class_exists($tmp)) {
					$def->class = $tmp;
					trigger_error("You should use @return \\$tmp' in $reflection.", E_USER_WARNING);
				}
			}

		} elseif ($service = $this->getServiceName($factory)) { // alias or factory
			if (!$def->implement) {
				$def->autowired = FALSE;
			}
			if (Strings::contains($service, '\\')) { // @\Class
				return $def->class = $service;
			}
			if ($this->definitions[$service]->implement) {
				$def->autowired = FALSE;
			}
			return $def->class = $this->definitions[$service]->implement ?: $this->resolveClass($service, $recursive);

		} else {
			return $def->class = $factory; // class name
		}
	}


	/**
	 * Adds a file to the list of dependencies.
	 * @return self
	 */
	public function addDependency($file)
	{
		$this->dependencies[$file] = TRUE;
		return $this;
	}


	/**
	 * Returns the list of dependent files.
	 * @return array
	 */
	public function getDependencies()
	{
		unset($this->dependencies[FALSE]);
		return array_keys($this->dependencies);
	}


	/********************* code generator ****************d*g**/


	/**
	 * Generates PHP classes. First class is the container.
	 * @return Nette\PhpGenerator\ClassType[]
	 */
	public function generateClasses($className = 'Container', $parentName = 'Nette\DI\Container')
	{
		unset($this->definitions[self::THIS_CONTAINER]);
		$this->addDefinition(self::THIS_CONTAINER)->setClass('Nette\DI\Container');

		$this->generatedClasses = array();
		$this->prepareClassList();

		$containerClass = $this->generatedClasses[] = new Nette\PhpGenerator\ClassType($className);
		$containerClass->setExtends($parentName);
		$containerClass->addMethod('__construct')
			->addBody('parent::__construct(?);', array($this->parameters));

		$definitions = $this->definitions;
		ksort($definitions);

		$meta = $containerClass->addProperty('meta', array())
			->setVisibility('protected')
			->setValue(array(Container::TYPES => $this->classes));

		foreach ($definitions as $name => $def) {
			foreach ($def->tags as $tag => $value) {
				$meta->value[Container::TAGS][$tag][$name] = $value;
			}
		}

		foreach ($definitions as $name => $def) {
			try {
				$name = (string) $name;
				$methodName = Container::getMethodName($name);
				if (!PhpHelpers::isIdentifier($methodName)) {
					throw new ServiceCreationException('Name contains invalid characters.');
				}
				$containerClass->addMethod($methodName)
					->addDocument("@return " . ($def->implement ?: $def->class))
					->setBody($name === self::THIS_CONTAINER ? 'return $this;' : $this->generateService($name))
					->setParameters($def->implement ? array() : $this->convertParameters($def->parameters));
			} catch (\Exception $e) {
				throw new ServiceCreationException("Service '$name': " . $e->getMessage(), NULL, $e);
			}
		}

		return $this->generatedClasses;
	}


	/**
	 * Generates body of service method.
	 * @return string
	 */
	private function generateService($name)
	{
		$this->currentService = NULL;
		$def = $this->definitions[$name];

		$serviceRef = $this->getServiceName($def->factory->entity);
		$factory = $serviceRef && !$def->factory->arguments && !$def->setup && $def->implementType !== 'create'
			? new Statement(array('@' . ContainerBuilder::THIS_CONTAINER, 'getService'), array($serviceRef))
			: $def->factory;

		$code = '$service = ' . $this->formatStatement($factory) . ";\n";
		$this->currentService = $name;

		if ($def->class && $def->class !== $def->factory->entity && !$serviceRef) {
			$code .= PhpHelpers::formatArgs("if (!\$service instanceof $def->class) {\n"
				. "\tthrow new Nette\\UnexpectedValueException(?);\n}\n",
				array("Unable to create service '$name', value returned by factory is not $def->class type.")
			);
		}

		$setups = (array) $def->setup;
		if ($def->inject && $def->class) {
			$injects = array();
			foreach (Helpers::getInjectProperties(Reflection\ClassType::from($def->class)) as $property => $type) {
				$injects[] = new Statement('$' . $property, array('@\\' . ltrim($type, '\\')));
			}

			foreach (get_class_methods($def->class) as $method) {
				if (substr($method, 0, 6) === 'inject') {
					$injects[] = new Statement($method);
				}
			}

			foreach ($injects as $inject) {
				foreach ($setups as $key => $setup) {
					if ($setup->entity === $inject->entity) {
						$inject = $setup;
						unset($setups[$key]);
					}
				}
				array_unshift($setups, $inject);
			}
		}

		foreach ($setups as $setup) {
			if (is_string($setup->entity) && strpbrk($setup->entity, ':@?') === FALSE) { // auto-prepend @self
				$setup->entity = array('@self', $setup->entity);
			}
			$code .= $this->formatStatement($setup) . ";\n";
		}

		$code .= 'return $service;';

		if (!$def->implement) {
			return $code;
		}

		$factoryClass = $this->generatedClasses[] = new Nette\PhpGenerator\ClassType;
		$factoryClass->setName(str_replace(array('\\', '.'), '_', "{$this->generatedClasses[0]->name}_{$def->implement}Impl_{$name}"))
			->addImplement($def->implement)
			->setFinal(TRUE);

		$factoryClass->addProperty('container')
			->setVisibility('private');

		$factoryClass->addMethod('__construct')
			->addBody('$this->container = $container;')
			->addParameter('container')
				->setTypeHint('Nette\DI\Container');

		$factoryClass->addMethod($def->implementType)
			->setParameters($this->convertParameters($def->parameters))
			->setBody(str_replace('$this', '$this->container', $code));

		return "return new {$factoryClass->name}(\$this);";
	}


	/**
	 * Converts parameters from ServiceDefinition to PhpGenerator.
	 * @return Nette\PhpGenerator\Parameter[]
	 */
	private function convertParameters(array $parameters)
	{
		$res = array();
		foreach ($parameters as $k => $v) {
			$tmp = explode(' ', is_int($k) ? $v : $k);
			$param = $res[] = new Nette\PhpGenerator\Parameter;
			$param->setName(end($tmp));
			if (!is_int($k)) {
				$param = $param->setOptional(TRUE)->setDefaultValue($v);
			}
			if (isset($tmp[1])) {
				$param->setTypeHint($tmp[0]);
			}
		}
		return $res;
	}


	/**
	 * Formats PHP code for class instantiating, function calling or property setting in PHP.
	 * @return string
	 * @internal
	 */
	public function formatStatement(Statement $statement)
	{
		$entity = $this->normalizeEntity($statement->entity);
		$arguments = $statement->arguments;

		if (is_string($entity) && Strings::contains($entity, '?')) { // PHP literal
			return $this->formatPhp($entity, $arguments);

		} elseif ($service = $this->getServiceName($entity)) { // factory calling
			$params = array();
			foreach ($this->definitions[$service]->parameters as $k => $v) {
				$params[] = preg_replace('#\w+\z#', '\$$0', (is_int($k) ? $v : $k)) . (is_int($k) ? '' : ' = ' . PhpHelpers::dump($v));
			}
			$rm = new Reflection\GlobalFunction(create_function(implode(', ', $params), ''));
			$arguments = Helpers::autowireArguments($rm, $arguments, $this);
			return $this->formatPhp('$this->?(?*)', array(Container::getMethodName($service), $arguments));

		} elseif ($entity === 'not') { // operator
			return $this->formatPhp('!?', array($arguments[0]));

		} elseif (is_string($entity)) { // class name
			if ($constructor = Reflection\ClassType::from($entity)->getConstructor()) {
				$this->addDependency($constructor->getFileName());
				$arguments = Helpers::autowireArguments($constructor, $arguments, $this);
			} elseif ($arguments) {
				throw new ServiceCreationException("Unable to pass arguments, class $entity has no constructor.");
			}
			return $this->formatPhp("new $entity" . ($arguments ? '(?*)' : ''), array($arguments));

		} elseif (!Nette\Utils\Arrays::isList($entity) || count($entity) !== 2) {
			throw new ServiceCreationException("Expected class, method or property, " . PhpHelpers::dump($entity) . " given.");

		} elseif ($entity[0] === '') { // globalFunc
			return $this->formatPhp("$entity[1](?*)", array($arguments));

		} elseif (Strings::contains($entity[1], '$')) { // property setter
			Validators::assert($arguments, 'list:1', "setup arguments for '" . Nette\Utils\Callback::toString($entity) . "'");
			if ($this->getServiceName($entity[0])) {
				return $this->formatPhp('?->? = ?', array($entity[0], substr($entity[1], 1), $arguments[0]));
			} else {
				return $this->formatPhp($entity[0] . '::$? = ?', array(substr($entity[1], 1), $arguments[0]));
			}

		} elseif ($service = $this->getServiceName($entity[0])) { // service method
			$class = $this->definitions[$service]->implement;
			if (!$class || !method_exists($class, $entity[1])) {
				$class = $this->definitions[$service]->class;
			}
			if ($class) {
				$arguments = $this->autowireArguments($class, $entity[1], $arguments);
			}
			return $this->formatPhp('?->?(?*)', array($entity[0], $entity[1], $arguments));

		} else { // static method
			$arguments = $this->autowireArguments($entity[0], $entity[1], $arguments);
			return $this->formatPhp("$entity[0]::$entity[1](?*)", array($arguments));
		}
	}


	/**
	 * Formats PHP statement.
	 * @return string
	 */
	public function formatPhp($statement, $args)
	{
		$that = $this;
		array_walk_recursive($args, function(& $val) use ($that) {
			if ($val instanceof Statement) {
				$val = ContainerBuilder::literal($that->formatStatement($val));

			} elseif ($val === $that) {
				$val = ContainerBuilder::literal('$this');

			} elseif ($val instanceof ServiceDefinition) {
				$val = '@' . current(array_keys($that->definitions, $val, TRUE));

			} elseif (is_string($val) && preg_match('#^[\w\\\\]*::[A-Z][A-Z0-9_]*\z#', $val, $m)) {
				$val = ContainerBuilder::literal(ltrim($val, ':'));
			}

			if (is_string($val) && substr($val, 0, 1) === '@') {
				$pair = explode('::', $val, 2);
				$name = $that->getServiceName($pair[0]);
				if (isset($pair[1]) && preg_match('#^[A-Z][A-Z0-9_]*\z#', $pair[1], $m)) {
					$val = $that->definitions[$name]->class . '::' . $pair[1];
				} else {
					if ($name === ContainerBuilder::THIS_CONTAINER) {
						$val = '$this';
					} elseif ($name === $that->currentService) {
						$val = '$service';
					} else {
						$val = $that->formatStatement(new Statement(array('@' . ContainerBuilder::THIS_CONTAINER, 'getService'), array($name)));
					}
					$val .= (isset($pair[1]) ? PhpHelpers::formatArgs('->?', array($pair[1])) : '');
				}
				$val = ContainerBuilder::literal($val);
			}
		});
		return PhpHelpers::formatArgs($statement, $args);
	}


	/**
	 * Expands %placeholders% in strings.
	 * @return mixed
	 */
	public function expand($value)
	{
		return Helpers::expand($value, $this->parameters);
	}


	/**
	 * @return Nette\PhpGenerator\PhpLiteral
	 */
	public static function literal($phpCode)
	{
		return new Nette\PhpGenerator\PhpLiteral($phpCode);
	}


	/** @internal */
	public function normalizeEntity($entity)
	{
		if (is_string($entity) && Strings::contains($entity, '::') && !Strings::contains($entity, '?')) { // Class::method -> [Class, method]
			$entity = explode('::', $entity);
		}

		if (is_array($entity) && $entity[0] instanceof ServiceDefinition) { // [ServiceDefinition, ...] -> [@serviceName, ...]
			$entity[0] = '@' . current(array_keys($this->definitions, $entity[0], TRUE));

		} elseif ($entity instanceof ServiceDefinition) { // ServiceDefinition -> @serviceName
			$entity = '@' . current(array_keys($this->definitions, $entity, TRUE));

		} elseif (is_array($entity) && $entity[0] === $this) { // [$this, ...] -> [@container, ...]
			$entity[0] = '@' . ContainerBuilder::THIS_CONTAINER;
		}
		return $entity; // Class, @service, [Class, member], [@service, member], [, globalFunc]
	}


	/**
	 * Converts @service or @\Class -> service name and checks its existence.
	 * @return string  of FALSE, if argument is not service name
	 */
	public function getServiceName($arg)
	{
		if (!is_string($arg) || !preg_match('#^@[\w\\\\.].*\z#', $arg)) {
			return FALSE;
		}
		$service = substr($arg, 1);
		if ($service === self::THIS_SERVICE) {
			$service = $this->currentService;
		}
		if (Strings::contains($service, '\\')) {
			if ($this->classes === FALSE) { // may be disabled by prepareClassList
				return $service;
			}
			$res = $this->getByType($service);
			if (!$res) {
				throw new ServiceCreationException("Reference to missing service of type $service.");
			}
			return $res;
		}
		if (!isset($this->definitions[$service])) {
			throw new ServiceCreationException("Reference to missing service '$service'.");
		}
		return $service;
	}


	/** @deprecated */
	function generateClass()
	{
		throw new Nette\DeprecatedException(__METHOD__ . '() is deprecated; use generateClasses()[0] instead.');
	}

}
