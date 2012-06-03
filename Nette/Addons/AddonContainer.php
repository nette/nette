<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Addons;

use Nette;
use Nette\Application\Application;
use Nette\Application\Request;
use Nette\Application\IResponse;
use Nette\Config\Compiler;
use Nette\Config\Configurator;
use Nette\DI\Container;



/**
 * Addon container.
 * 
 * @author     Jan Jakes
 */
class AddonContainer extends Nette\Object
{

	/** @var Addon[] */
	private $addons = array();

	/** @var Addon[] */
	private $classMap = array();



	/**
	 * @param  Addon[]
	 */
	public function __construct($addons)
	{
		foreach ($addons as $name => $addon) {
			if (!($addon instanceof Addon)) {
				throw new Nette\InvalidArgumentException("Class '" . get_class($addon) . "' is not a subclass of Nette\Addons\Addon.");
			}

			if (is_string($name)) {
				$addon->setName($name);
			} else {
				$name = $addon->getName();
			}

			if (isset($this->addons[$name])) {
				throw new Nette\InvalidStateException("Addon '$name' already defined.");
			}

			$class = get_class($addon);
			if (isset($this->classMap[$class])) {
				throw new Nette\InvalidStateException("Addon of type '$class' already defined.");
			}

			$this->addons[$name] = $this->classMap[$class] = $addon;			
		}
	}



	/**
	 * @param  string
	 * @return Addon
	 */
	public function getAddon($name)
	{
		if (!isset($this->addons[$name])) {
			throw new Nette\InvalidStateException("Addon '$name' not found.");
		}

		return $this->addons[$name];
	}



	/**
	 * @return Addon[]
	 */
	public function getAddons()
	{
		return $this->addons;
	}



	/**
	 * @param  string
	 */
	public function getAddonByType($class)
	{
		if (!isset($this->classMap[$class])) {
			throw new Nette\InvalidStateException("Addon of type '$class' not found.");
		}

		return $this->classMap[$class];
	}



	/**
	 * @param  Configurator
	 * @param  Compiler
	 */
	public function compile(Configurator $config, Compiler $compiler)
	{
		foreach ($this->addons as $addon) {
			$addon->compile($config, $compiler/*, $this*/);
		}
	}

}
