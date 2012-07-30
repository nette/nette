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

	/** @var AddonContainer */
	private $addons;



	/**
	 * @param  AddonContainer
	 */
	public function setAddons(AddonContainer $addons)
	{
		$this->addons = $addons;
	}



	/**
	 * @param  string
	 * @return Addon
	 */
	public function getAddon($name)
	{
		return $this->addons->getAddon($name);
	}



	/**
	 * @return Addon[]
	 */
	public function getAddons()
	{
		return $this->addons->getAddons();
	}



	/**
	 * @param  string
	 */
	public function getAddonByType($class)
	{
		return $this->addons->getAddonByType($class);
	}



	/**
	 * @return AddonContainer
	 */
	public function getContainer()
	{
		return $this->addons;
	}

}
