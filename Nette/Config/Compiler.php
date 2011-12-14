<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
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
 * @property-read array $extensions
 * @property-read array $config
 * @property-read Nette\DI\ContainerBuilder $container
 */
class Compiler extends Nette\Object
{
	/** @var array of CompilerExtension */
	private $extensions = array();

	/** @var Nette\DI\ContainerBuilder */
	private $container;

	/** @var array */
	private $config;

	/** @var array reserved section names */
	private static $reserved = array('services' => 1, 'factories' => 1, 'parameters' => 1);



	/**
	 * Add custom configurator extension.
	 * @return ServiceDefinition
	 */
	public function addExtension($name, CompilerExtension $extension)
	{
		if (isset(self::$reserved[$name])) {
			throw new Nette\InvalidArgumentException("Name '$name' is reserved.");
		}
		$this->extensions[$name] = $extension;
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
	public function getContainer()
	{
		return $this->container;
	}



	/**
	 * @return array
	 */
	public function getConfig()
	{
		return $this->config;
	}



	/**
	 * @return string
	 */
	public function compile(array $config, $className)
	{
		$this->config = $config;
		$this->container = new Nette\DI\ContainerBuilder;
		$this->processParameters();
		$this->processExtensions();
		$this->processServices();
		return $this->generateCode($className);
	}



	public function processParameters()
	{
		if (isset($this->config['parameters'])) {
			$this->container->parameters = $this->config['parameters'];
		}
	}



	public function processExtensions()
	{
		if ($extra = array_diff_key($this->config, self::$reserved, $this->extensions)) {
			$extra = implode("', '", array_keys($extra));
			throw new Nette\InvalidStateException("Found sections '$extra' in configuration, but corresponding extensions are missing.");
		}

		$configExp = $this->container->expand($this->config);
		foreach ($this->extensions as $name => $extension) {
			$config = isset($configExp[$name]) ? $configExp[$name] : array();
			$extension->loadConfiguration($this->container, $config);
		}
	}



	public function processServices()
	{
		$this->parseServices($this->container, $this->config);
	}



	public function generateCode($className)
	{
		foreach ($this->extensions as $extension) {
			$extension->beforeCompile($this->container);
		}

		$class = $this->container->generateClass();
		$class->setName($className)
			->addMethod('initialize');

		foreach ($this->extensions as $extension) {
			$extension->afterCompile($this->container, $class);
		}
		return (string) $class;
	}



	/********************* tools ****************d*g**/



	/**
	 * Parses section 'services' from configuration file.
	 * @return void
	 */
	public static function parseServices(Nette\DI\ContainerBuilder $container, array $config)
	{
		$all = isset($config['services']) ? $config['services'] : array();
		$all += isset($config['factories']) ? $config['factories'] : array();

		uasort($all, function($a, $b) {
			return strcmp(Helpers::isInheriting($a), Helpers::isInheriting($b));
		});

		foreach ($all as $name => $def) {
			if ($parent = Helpers::takeParent($def)) {
				$container->removeDefinition($name);
				$definition = $container->addDefinition($name);
				if ($parent !== Helpers::OVERWRITE) {
					foreach ($container->getDefinition($parent) as $k => $v) {
						$definition->$k = $v;
					}
				}
			} elseif ($container->hasDefinition($name)) {
				$definition = $container->getDefinition($name);
			} else {
				$definition = $container->addDefinition($name);
			}
			try {
				static::parseService($definition, $def, isset($config['services'][$name]));
			} catch (\Exception $e) {
				throw new Nette\DI\ServiceCreationException("Service '$name': " . $e->getMessage()/**/, NULL, $e/**/);
			}
		}
	}



	/**
	 * Parses single service from configuration file.
	 * @return void
	 */
	public static function parseService(Nette\DI\ServiceDefinition $definition, $config, $shared = TRUE)
	{
		if (!is_array($config)) {
			$config = array('class' => $config);
		}

		$known = $shared
			? array('class', 'factory', 'arguments', 'setup', 'autowired', 'run', 'tags')
			: array('class', 'factory', 'arguments', 'setup', 'autowired', 'internal', 'parameters');

		if ($error = array_diff(array_keys($config), $known)) {
			throw new Nette\InvalidStateException("Unknown key '" . implode("', '", $error) . "' in definition of service.");
		}

		$arguments = array();
		if (isset($config['arguments'])) {
			Validators::assertField($config, 'arguments', 'array');
			$arguments = self::filterArguments($config['arguments']);
		}

		if (isset($config['class'])) {
			Validators::assertField($config, 'class', 'string|stdClass');
			if ($config['class'] instanceof \stdClass) {
				$definition->setClass($config['class']->value, self::filterArguments($config['class']->attributes));
			} else {
				$definition->setClass($config['class'], $arguments);
			}
		}

		if (isset($config['factory'])) {
			Validators::assertField($config, 'factory', 'callable|stdClass');
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
					if (strpos(is_array($setup->value) ? implode('', $setup->value) : $setup->value, '$') === FALSE) {
						$definition->addSetup($setup->value, self::filterArguments($setup->attributes));
					} else {
						Validators::assert($setup->attributes, 'list:1', "setup arguments for '$setup->value'");
						$definition->addSetup($setup->value, $setup->attributes[0]);
					}
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
			Validators::assertField($config, 'autowired', 'bool|string');
			$definition->setAutowired($config['autowired']);
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



	private static function filterArguments(array $args)
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
