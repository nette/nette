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
	Nette\DI\ContainerBuilder;



/**
 * Configurator compiling extension.
 *
 * @author     David Grudl
 */
abstract class CompilerExtension extends Nette\Object
{
	/** @var Compiler */
	protected $compiler;

	/** @var string */
	protected $name;



	public function setCompiler(Compiler $compiler, $name)
	{
		$this->compiler = $compiler;
		$this->name = $name;
		return $this;
	}



	/**
	 * Returns extension configuration without expanded parameters.
	 * @return array
	 */
	public function getConfig()
	{
		$config = $this->compiler->getConfig();
		return isset($config[$this->name]) ? $config[$this->name] : array();
	}



	/**
	 * Prepend extension name to identifier or service name.
	 * @param  string
	 * @return string
	 */
	public function prefix($id)
	{
		return substr_replace($id, $this->name . '_', $id[0] === '@' ? 1 : 0, 0);
	}



	/**
	 * Processes configuration data. Intended to be overridden by descendant.
	 * @param  ContainerBuilder builded DI container
	 * @param  array  configuration with expanded parameters
	 * @return void
	 */
	public function loadConfiguration(ContainerBuilder $container, array $config)
	{
	}



	/**
	 * Adjusts DI container before is compiled to PHP class. Intended to be overridden by descendant.
	 * @return void
	 */
	public function beforeCompile(ContainerBuilder $container)
	{
	}



	/**
	 * Adjusts DI container compiled to PHP class. Intended to be overridden by descendant.
	 * @return void
	 */
	public function afterCompile(ContainerBuilder $container, Nette\Utils\PhpGenerator\ClassType $class)
	{
	}

}
