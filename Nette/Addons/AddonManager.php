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


/**
 * Addon manager.
 * 
 * @author     Jan Jakes
 */
class AddonManager
{

	/** @var Addon[] */
	private $addons = array();

	/** @var Addon[] */
	private $classMap = array();



	/**
	 * @param  Addon
	 */
	public function registerAddon(Addon $addon)
	{
		$name = $addon->getName();
		if (isset($this->addons[$name])) {
			throw new Nette\InvalidStateException("Addon '$name' already defined.");
		}

		$class = get_class($addon);
		if (isset($this->classMap[$class])) {
			throw new Nette\InvalidStateException("Addon of type '$class' already defined.");
		}

		$this->addons[$name] = $this->classMap[$class] = $addon;
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
		$this->addons;
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

}
