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



	/**
	 * Add custom configurator extension.
	 * @return ServiceDefinition
	 */
	public function addExtension($name, CompilerExtension $extension)
	{
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
			unset($this->config['parameters']);
		}
	}



	public function processExtensions()
	{
		$configExp = $this->container->expand($this->config);
		foreach ($this->extensions as $name => $extension) {
			$extension->loadConfiguration($this->container, isset($configExp[$name]) ? $configExp[$name] : array());
			unset($configExp[$name]);
		}

		// missing extensions simply put to parameters
		unset($configExp['services']);
		$this->container->parameters += array_intersect_key($this->config, $configExp);
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
		if (!isset($config['services'])) {
			return;
		}

		uasort($config['services'], function($a, $b) {
			return strcmp(Config::isInheriting($a), Config::isInheriting($b));
		});

		foreach ($config['services'] as $name => $def) {
			if ($parent = Config::takeParent($def)) {
				$container->removeDefinition($name);
				$definition = $container->addDefinition($name);
				if ($parent !== Config::OVERWRITE) {
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
				static::parseService($definition, $def);
			} catch (\Exception $e) {
				throw new Nette\DI\ServiceCreationException("Service '$name': " . $e->getMessage()/**/, NULL, $e/**/);
			}
		}
	}



	/**
	 * Parses single service from configuration file.
	 * @return void
	 */
	public static function parseService(Nette\DI\ServiceDefinition $definition, $config)
	{
		if (!is_array($config)) {
			$config = array('class' => $config);
		}

		$known = array('class', 'factory', 'arguments', 'autowired', 'setup', 'run', 'tags');
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
			Validators::assertField($config, 'setup', 'array');
			if (Config::takeParent($config['setup'])) {
				$definition->setup = array();
			}
			foreach ($config['setup'] as $member => $args) {
				if (is_int($member)) {
					Validators::assert($args, 'list:1..2', "setup item #$member");
					$member = $args[0];
					$args = isset($args[1]) ? $args[1] : NULL;
				}
				if (strpos($member, '$') === FALSE && $args !== NULL) {
					Validators::assert($args, 'array', "setup arguments for '$member'");
					$args = array_diff($args, array('...'));
				}
				$definition->addSetup($member, $args);
			}
		}

		if (isset($config['autowired'])) {
			Validators::assertField($config, 'autowired', 'bool|string');
			$definition->setAutowired($config['autowired']);
		}

		if (isset($config['run'])) {
			$config['tags']['run'] = (bool) $config['run'];
		}

		if (isset($config['tags'])) {
			Validators::assertField($config, 'tags', 'array');
			if (Config::takeParent($config['tags'])) {
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
