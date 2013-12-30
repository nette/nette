<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette;

use Nette,
	Nette\DI;


/**
 * Initial system DI container generator.
 *
 * @author     David Grudl
 *
 * @property   bool $debugMode
 * @property-write $tempDirectory
 */
class Configurator extends Object
{
	/** @deprecated */
	const DEVELOPMENT = 'development',
		PRODUCTION = 'production',
		AUTO = TRUE,
		NONE = FALSE;

	/** @var array of function(Configurator $sender, DI\Compiler $compiler); Occurs after the compiler is created */
	public $onCompile;

	/** @var array */
	public $defaultExtensions = array(
		'php' => 'Nette\DI\Extensions\PhpExtension',
		'constants' => 'Nette\DI\Extensions\ConstantsExtension',
		'nette' => 'Nette\DI\Extensions\NetteExtension',
		'extensions' => 'Nette\DI\Extensions\ExtensionsExtension',
	);

	/** @var array */
	protected $parameters;

	/** @var array */
	protected $files = array();


	public function __construct()
	{
		$this->parameters = $this->getDefaultParameters();
	}


	/**
	 * Set parameter %debugMode%.
	 * @param  bool|string|array
	 * @return self
	 */
	public function setDebugMode($value = TRUE)
	{
		$this->parameters['debugMode'] = is_string($value) || is_array($value) ? static::detectDebugMode($value) : (bool) $value;
		$this->parameters['productionMode'] = !$this->parameters['debugMode']; // compatibility
		return $this;
	}


	/**
	 * @return bool
	 */
	public function isDebugMode()
	{
		return $this->parameters['debugMode'];
	}


	/**
	 * Sets path to temporary directory.
	 * @return self
	 */
	public function setTempDirectory($path)
	{
		$this->parameters['tempDir'] = $path;
		return $this;
	}


	/**
	 * Adds new parameters. The %params% will be expanded.
	 * @return self
	 */
	public function addParameters(array $params)
	{
		$this->parameters = DI\Config\Helpers::merge($params, $this->parameters);
		return $this;
	}


	/**
	 * @return array
	 */
	protected function getDefaultParameters()
	{
		$trace = debug_backtrace(PHP_VERSION_ID >= 50306 ? DEBUG_BACKTRACE_IGNORE_ARGS : FALSE);
		$debugMode = static::detectDebugMode();
		return array(
			'appDir' => isset($trace[1]['file']) ? dirname($trace[1]['file']) : NULL,
			'wwwDir' => isset($_SERVER['SCRIPT_FILENAME'])
				? dirname(realpath($_SERVER['SCRIPT_FILENAME']))
				: NULL,
			'debugMode' => $debugMode,
			'productionMode' => !$debugMode,
			'environment' => $debugMode ? 'development' : 'production',
			'consoleMode' => PHP_SAPI === 'cli',
			'container' => array(
				'class' => 'SystemContainer',
				'parent' => 'Nette\DI\Container',
			)
		);
	}


	/**
	 * @param  string        error log directory
	 * @param  string        administrator email
	 * @return void
	 */
	public function enableDebugger($logDirectory = NULL, $email = NULL)
	{
		Nette\Diagnostics\Debugger::$strictMode = TRUE;
		Nette\Diagnostics\Debugger::enable(!$this->parameters['debugMode'], $logDirectory, $email);
	}


	/**
	 * @return Nette\Loaders\RobotLoader
	 */
	public function createRobotLoader()
	{
		$loader = new Nette\Loaders\RobotLoader;
		$loader->setCacheStorage(new Nette\Caching\Storages\FileStorage($this->getCacheDirectory()));
		$loader->autoRebuild = $this->parameters['debugMode'];
		return $loader;
	}


	/**
	 * Adds configuration file.
	 * @return self
	 */
	public function addConfig($file, $section = NULL)
	{
		$this->files[] = array($file, $section === self::AUTO ? $this->parameters['environment'] : $section);
		return $this;
	}


	/**
	 * Returns system DI container.
	 * @return \SystemContainer
	 */
	public function createContainer()
	{
		$cache = new Nette\Caching\Cache(new Nette\Caching\Storages\PhpFileStorage($this->getCacheDirectory()), 'Nette.Configurator');
		$cacheKey = array($this->parameters, $this->files);
		$cached = $cache->load($cacheKey);
		if (!$cached) {
			$code = $this->buildContainer($dependencies);
			$cache->save($cacheKey, $code, array($cache::FILES => $dependencies));
			$cached = $cache->load($cacheKey);
		}
		require_once $cached['file'];

		$container = new $this->parameters['container']['class'];
		$container->initialize();
		Nette\Environment::setContext($container); // back compatibility
		return $container;
	}


	/**
	 * Build system container class.
	 * @return string
	 */
	protected function buildContainer(& $dependencies = NULL)
	{
		$loader = $this->createLoader();
		$config = array();
		$code = "<?php\n";
		foreach ($this->files as $tmp) {
			list($file, $section) = $tmp;
			$code .= "// source: $file $section\n";
			try {
				if ($section === NULL) { // back compatibility
					$config = DI\Config\Helpers::merge($loader->load($file, $this->parameters['environment']), $config);
					continue;
				}
			} catch (Nette\InvalidStateException $e) {
			} catch (Nette\Utils\AssertionException $e) {
			}

			$config = DI\Config\Helpers::merge($loader->load($file, $section), $config);
		}
		$code .= "\n";

		if (!isset($config['parameters'])) {
			$config['parameters'] = array();
		}
		$config['parameters'] = DI\Config\Helpers::merge($config['parameters'], $this->parameters);

		$compiler = $this->createCompiler();
		$this->onCompile($this, $compiler);

		$code .= $compiler->compile(
			$config,
			$this->parameters['container']['class'],
			$config['parameters']['container']['parent']
		);
		$dependencies = array_merge($loader->getDependencies(), $this->parameters['debugMode'] ? $compiler->getContainerBuilder()->getDependencies() : array());
		return $code;
	}


	/**
	 * @return Compiler
	 */
	protected function createCompiler()
	{
		$compiler = new DI\Compiler;

		foreach ($this->defaultExtensions as $name => $class) {
			$compiler->addExtension($name, new $class);
		}

		return $compiler;
	}


	/**
	 * @return Loader
	 */
	protected function createLoader()
	{
		return new DI\Config\Loader;
	}


	protected function getCacheDirectory()
	{
		if (empty($this->parameters['tempDir'])) {
			throw new Nette\InvalidStateException("Set path to temporary directory using setTempDirectory().");
		}
		$dir = $this->parameters['tempDir'] . '/cache';
		if (!is_dir($dir)) {
			mkdir($dir);
		}
		return $dir;
	}


	/********************* tools ****************d*g**/


	/**
	 * Detects debug mode by IP address.
	 * @param  string|array  IP addresses or computer names whitelist detection
	 * @return bool
	 */
	public static function detectDebugMode($list = NULL)
	{
		$list = is_string($list) ? preg_split('#[,\s]+#', $list) : (array) $list;
		if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$list[] = '127.0.0.1';
			$list[] = '::1';
		}
		return in_array(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : php_uname('n'), $list, TRUE);
	}


	/** @deprecated */
	public function setProductionMode($value = TRUE)
	{
		trigger_error(__METHOD__ . '() is deprecated; use setDebugMode(!$value) instead.', E_USER_DEPRECATED);
		return $this->setDebugMode(is_bool($value) ? !$value : $value);
	}


	/** @deprecated */
	public function isProductionMode()
	{
		trigger_error(__METHOD__ . '() is deprecated; use !isDebugMode() instead.', E_USER_DEPRECATED);
		return !$this->isDebugMode();
	}


	/** @deprecated */
	public static function detectProductionMode($list = NULL)
	{
		trigger_error(__METHOD__ . '() is deprecated; use !detectDebugMode() instead.', E_USER_DEPRECATED);
		return !static::detectDebugMode($list);
	}

}
