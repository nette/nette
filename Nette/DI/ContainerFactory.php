<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\DI;

use Nette;


/**
 * DI container generator.
 *
 * @author     David Grudl
 */
class ContainerFactory extends Nette\Object
{
	/** @var array of function(ContainerFactory $factory, Compiler $compiler, $config); Occurs after the compiler is created */
	public $onCompile;

	/** @var bool */
	public $autoRebuild = FALSE;

	/** @var string */
	public $class = 'SystemContainer';

	/** @var string */
	public $parentClass = 'Nette\DI\Container';

	/** @var array */
	public $config = array();

	/** @var array [file, section] */
	public $configFiles = array();

	/** @var string */
	public $tempDirectory;

	/** @var array */
	private $dependencies = array();


	public function __construct($tempDirectory)
	{
		$this->tempDirectory = $tempDirectory;
	}


	/**
	 * @return Container
	 */
	public function create()
	{
		if (!class_exists($this->class)) {
			$this->loadClass();
		}
		return new $this->class;
	}


	/**
	 * @return string
	 */
	protected function generateCode()
	{
		$compiler = $this->createCompiler();
		$config = $this->generateConfig();
		$this->onCompile($this, $compiler, $config);

		$code = "<?php\n";
		foreach ($this->configFiles as $info) {
			$code .= "// source: $info[0] $info[1]\n";
		}
		$code .= "\n" . $compiler->compile($config, $this->class, $this->parentClass);

		if ($this->autoRebuild !== 'compat') { // back compatibility
			$this->dependencies = array_merge($this->dependencies, $compiler->getContainerBuilder()->getDependencies());
		}
		return $code;
	}


	/**
	 * @return array
	 */
	protected function generateConfig()
	{
		$config = array();
		$loader = $this->createLoader();
		foreach ($this->configFiles as $info) {
			$config = Config\Helpers::merge($loader->load($info[0], $info[1]), $config);
		}
		$this->dependencies = array_merge($this->dependencies, $loader->getDependencies());

		return Config\Helpers::merge($config, $this->config);
	}


	/**
	 * @return void
	 */
	private function loadClass()
	{
		$cache = new Nette\Caching\Cache(new Nette\Caching\Storages\PhpFileStorage($this->tempDirectory), 'Nette.DI');
		$cacheKey = array($this->config, $this->configFiles);
		$cached = $cache->load($cacheKey);
		if (!$cached) {
			$this->dependencies = array();
			$code = $this->generateCode();
			$cache->save($cacheKey, $code, array($cache::FILES => $this->dependencies));
			$cached = $cache->load($cacheKey);
		}
		require $cached['file'];
	}


	/**
	 * @return Compiler
	 */
	protected function createCompiler()
	{
		return new Compiler;
	}


	/**
	 * @return Config\Loader
	 */
	protected function createLoader()
	{
		return new Config\Loader;
	}

}
