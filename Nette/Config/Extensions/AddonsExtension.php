<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Config\Extensions;

use Nette;
use Nette\Config\Configurator;
use Nette\DI\ContainerBuilder;



/**
 * Nette addons extension.
 * 
 * @author     Jan Jakes
 */
class AddonsExtension extends Nette\Config\CompilerExtension
{

	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();

		$container->addDefinition('addonManager')
			->setClass('Nette\Addons\AddonManager');
	}

}
