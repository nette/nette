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

	/**
	 * Processes configuration data.
	 * @param  ContainerBuilder builded DI container
	 * @param  array  configuration (with expanded parameters)
	 * @return void
	 */
	public function loadConfiguration(ContainerBuilder $container, array $config)
	{
	}

	/**
	 * Adjusts DI container before is compiled to PHP class.
	 * @return void
	 */
	public function beforeCompile(ContainerBuilder $container)
	{
	}

	/**
	 * Adjusts DI container compiled to PHP class.
	 * @return void
	 */
	public function afterCompile(ContainerBuilder $container, Nette\Utils\PhpGenerator\ClassType $class)
	{
	}

}
