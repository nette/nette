<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Config;

use Nette,
	Nette\Utils\Validators;



/**
 * DI container compiler.
 *
 * @author     David Grudl
 *
 * @property-read CompilerExtension[] $extensions
 * @property-read Nette\DI\ContainerBuilder $containerBuilder
 * @property-read array $config
 */
class Compiler extends Nette\Object
{
	/** @var CompilerExtension[] */
	private $extensions = array();

	/** @var Nette\DI\ContainerBuilder */
	private $container;

	/** @var array */
	private $config;

	/** @var array reserved section names */
	private static $reserved = array('services' => 1, 'factories' => 1, 'parameters' => 1);



	/**
	 * Add custom configurator extension.
	 * @return Compiler  provides a fluent interface
	 */
	public function addExtension($name, CompilerExtension $extension)
	{
		if (isset(self::$reserved[$name])) {
			throw new Nette\InvalidArgumentException("Name '$name' is reserved.");
		}
		$this->extensions[$name] = $extension->setCompiler($this, $name);
		return $this;
	}



	/**
	 * @return array
	 */
	public function getExtensions()
	{
		return $this->extensions;
	}



	/**
	 * @return Nette\DI\ContainerBuilder
	 */
	public function getContainerBuilder()
	{
		return $this->container;
	}



	/**
	 * Returns configuration without expanded parameters.
	 * @return array
	 */
	public function getConfig()
	{
		return $this->config;
	}



	/**
	 * @return string
	 */
	public function compile(array $config, $className, $parentName)
	{
		$this->config = $config;
		$this->container = new Nette\DI\ContainerBuilder;
		$this->processParameters();
		$this->processExtensions();
		$this->processServices();
		return $this->generateCode($className, $parentName);
	}



	public function processParameters()
	{
		if (isset($this->config['parameters'])) {
			$this->container->parameters = $this->config['parameters'];
		}
	}



	public function processExtensions()
	{
		for ($i = 0; $slice = array_slice($this->extensions, $i, 1); $i++) {
			reset($slice)->loadConfiguration();
		}

		if ($extra = array_diff_key($this->config, self::$reserved, $this->extensions)) {
			$extra = implode("', '", array_keys($extra));
			throw new Nette\InvalidStateException("Found sections '$extra' in configuration, but corresponding extensions are missing.");
		}
	}



	public function processServices()
	{
		$this->parseServices($this->container, $this->config);

		foreach ($this->extensions as $name => $extension) {
			if (isset($this->config[$name])) {
				$this->parseServices($this->container, $this->config[$name], $name);
			}
		}

		foreach ($this->container->getDefinitions() as $name => $def) {
			if ($def->shared || !$def->factory || !is_string($def->factory->entity) || !interface_exists($def->factory->entity)) {
				continue;
			}

			$factoryType = Nette\Reflection\ClassType::from($def->factory->entity);
			if (!$factoryType->hasMethod('create')) {
				throw new Nette\InvalidStateException("Method $factoryType::create() in factory of '$name' must be defined.");
			}

			$factoryMethod = $factoryType->getMethod('create');
			if ($factoryMethod->isStatic()) {
				throw new Nette\InvalidStateException("Method $factoryMethod in factory of '$name' must not be static.");
			}

			if (count($factoryType->getMethods()) > 1) {
				$extra = array_diff(get_class_methods($factoryType->getName()), array('create'));
				throw new Nette\InvalidStateException("The interface $factoryType can contain only create() method. Methods " . implode(', ', $extra) . " are extra.");
			}

			$returnType = $factoryMethod->getAnnotation('return');
			if ($returnType && !class_exists($returnType)) {
				if ($returnType[0] !== '\\') {
					$returnType = $factoryType->getNamespaceName() . '\\' . $returnType;
				}
				if (!class_exists($returnType)) {
					throw new Nette\InvalidStateException("Please use a fully qualified name of class in @return annotation at $factoryMethod method. Class '$returnType' cannot be found.");
				}
			}

			if ($def->class === $def->factory->entity) {
				$def->class = NULL;
			}

			if (!$def->class) {
				if (!$returnType) {
					throw new Nette\InvalidStateException("Method $factoryMethod has no @return annotation.");

				} else {
					$def->class = $returnType;
				}

			} elseif ($returnType !== $def->class) {
				throw new Nette\InvalidStateException("Method $factoryMethod claims in @return annotation, that it returns instance of '$returnType', but factory definition demands '$def->class'.");
			}

			if (!$def->parameters && !$def->factory->arguments) {
				$createdClassConstructor = Nette\Reflection\ClassType::from($def->class)->getConstructor();
				foreach ($factoryMethod->getParameters() as $parameter) {
					$paramDef = ($parameter->getClassName() ? $parameter->getClassName() . ' ' : '') . $parameter->getName();
					foreach ($createdClassConstructor->getParameters() as $argument) {
						if ($parameter->getName() !== $argument->getName()) {
							continue;
						} elseif (($parameter->getClassName() || $argument->getClassName()) && $parameter->getClassName() !== $argument->getClassName()) {
							throw new \Nette\InvalidStateException("Argument $argument type hint doesn't match $parameter type hint.");
						} else {
							$def->parameters[] = $paramDef;
							$def->factory->arguments[$argument->position] = '%' . $argument->getName() . '%';
						}
					}
				}
			}

			$def->setCreates($def->class, $def->factory->arguments);
			$def->class = $def->factory->entity;
			$def->factory = NULL;
			$def->setShared(TRUE);
		}
	}



	public function generateCode($className, $parentName)
	{
		foreach ($this->extensions as $extension) {
			$extension->beforeCompile();
			$this->container->addDependency(Nette\Reflection\ClassType::from($extension)->getFileName());
		}

		$class = $this->container->generateClass($parentName);
		$class->setName($className)
			->addMethod('initialize');

		foreach ($this->extensions as $extension) {
			$extension->afterCompile($class);
		}

		$classes = $this->container->fetchGeneratedFactories();
		array_unshift($classes, $class);
		return implode("\n\n\n", $classes);
	}



	/********************* tools ****************d*g**/



	/**
	 * Parses section 'services' from configuration file.
	 * @return void
	 */
	public static function parseServices(Nette\DI\ContainerBuilder $container, array $config, $namespace = NULL)
	{
		$services = isset($config['services']) ? $config['services'] : array();
		$factories = isset($config['factories']) ? $config['factories'] : array();
		if ($tmp = array_intersect_key($services, $factories)) {
			$tmp = implode("', '", array_keys($tmp));
			throw new Nette\DI\ServiceCreationException("It is not allowed to use services and factories with the same names: '$tmp'.");
		}

		$all = $services + $factories;
		uasort($all, function($a, $b) {
			return strcmp(Helpers::isInheriting($a), Helpers::isInheriting($b));
		});

		foreach ($all as $name => $def) {
			$shared = array_key_exists($name, $services);
			$name = ($namespace ? $namespace . '.' : '') . $name;

			if (($parent = Helpers::takeParent($def)) && $parent !== $name) {
				$container->removeDefinition($name);
				$definition = $container->addDefinition($name);
				if ($parent !== Helpers::OVERWRITE) {
					foreach ($container->getDefinition($parent) as $k => $v) {
						$definition->$k = unserialize(serialize($v)); // deep clone
					}
				}
			} elseif ($container->hasDefinition($name)) {
				$definition = $container->getDefinition($name);
				if ($definition->shared !== $shared) {
					throw new Nette\DI\ServiceCreationException("It is not allowed to use service and factory with the same name '$name'.");
				}
			} else {
				$definition = $container->addDefinition($name);
			}
			try {
				static::parseService($definition, $def, $shared);
			} catch (\Exception $e) {
				throw new Nette\DI\ServiceCreationException("Service '$name': " . $e->getMessage(), NULL, $e);
			}
		}
	}



	/**
	 * Parses single service from configuration file.
	 * @return void
	 */
	public static function parseService(Nette\DI\ServiceDefinition $definition, $config, $shared = TRUE)
	{
		if ($config === NULL) {
			return;
		} elseif (!is_array($config)) {
			$config = array('class' => NULL, 'factory' => $config);
		}

		$known = $shared
			? array('class', 'factory', 'arguments', 'setup', 'autowired', 'inject', 'run', 'tags')
			: array('class', 'factory', 'arguments', 'setup', 'autowired', 'inject', 'internal', 'parameters');

		if ($error = array_diff(array_keys($config), $known)) {
			throw new Nette\InvalidStateException("Unknown key '" . implode("', '", $error) . "' in definition of service.");
		}

		$arguments = array();
		if (array_key_exists('arguments', $config)) {
			Validators::assertField($config, 'arguments', 'array');
			$arguments = self::filterArguments($config['arguments']);
			$definition->setArguments($arguments);
		}

		if (array_key_exists('class', $config) || array_key_exists('factory', $config)) {
			$definition->class = NULL;
			$definition->factory = NULL;
		}

		if (array_key_exists('class', $config)) {
			Validators::assertField($config, 'class', 'string|stdClass|null');
			if ($config['class'] instanceof \stdClass) {
				$definition->setClass($config['class']->value, self::filterArguments($config['class']->attributes));
			} else {
				$definition->setClass($config['class'], $arguments);
			}
		}

		if (array_key_exists('factory', $config)) {
			Validators::assertField($config, 'factory', 'callable|stdClass|null');
			if ($config['factory'] instanceof \stdClass) {
				$definition->setFactory($config['factory']->value, self::filterArguments($config['factory']->attributes));
			} else {
				$definition->setFactory($config['factory'], $arguments);
			}
		}

		if (isset($config['setup'])) {
			if (Helpers::takeParent($config['setup'])) {
				$definition->setup = array();
			}
			Validators::assertField($config, 'setup', 'list');
			foreach ($config['setup'] as $id => $setup) {
				Validators::assert($setup, 'callable|stdClass', "setup item #$id");
				if ($setup instanceof \stdClass) {
					Validators::assert($setup->value, 'callable', "setup item #$id");
					$definition->addSetup($setup->value, self::filterArguments($setup->attributes));
				} else {
					$definition->addSetup($setup);
				}
			}
		}

		$definition->setShared($shared);
		if (isset($config['parameters'])) {
			Validators::assertField($config, 'parameters', 'array');
			$definition->setParameters($config['parameters']);
		}

		if (isset($config['autowired'])) {
			Validators::assertField($config, 'autowired', 'bool');
			$definition->setAutowired($config['autowired']);
		}

		if (isset($config['inject'])) {
			Validators::assertField($config, 'inject', 'bool');
			$definition->setInject($config['inject']);
		}

		if (isset($config['internal'])) {
			Validators::assertField($config, 'internal', 'bool');
			$definition->setInternal($config['internal']);
		}

		if (isset($config['run'])) {
			$config['tags']['run'] = (bool) $config['run'];
		}

		if (isset($config['tags'])) {
			Validators::assertField($config, 'tags', 'array');
			if (Helpers::takeParent($config['tags'])) {
				$definition->tags = array();
			}
			foreach ($config['tags'] as $tag => $attrs) {
				if (is_int($tag) && is_string($attrs)) {
					$definition->addTag($attrs);
				} else {
					$definition->addTag($tag, $attrs);
				}
			}
		}
	}



	/**
	 * Removes ... and replaces entities with Nette\DI\Statement.
	 * @return array
	 */
	public static function filterArguments(array $args)
	{
		foreach ($args as $k => $v) {
			if ($v === '...') {
				unset($args[$k]);
			} elseif ($v instanceof \stdClass && isset($v->value, $v->attributes)) {
				$args[$k] = new Nette\DI\Statement($v->value, self::filterArguments($v->attributes));
			}
		}
		return $args;
	}

}
