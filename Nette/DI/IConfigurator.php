<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\DI;

use Nette;



/**
 * Nette\Environment helper interface.
 *
 * @author     David Grudl
 * @author     Patrik Votoček
 */
interface IConfigurator
{

	/**
	 * Detect environment mode.
	 * @param  string mode name
	 * @return bool
	 */
	public function detect($name);



	/**
	 * Loads global configuration from file and process it.
	 * @param  string  file name
	 * @return Nette\ArrayHash
	 */
	public function loadConfig($file);



	/**
	 * Get initial instance of context.
	 * @return IContainer
	 */
	public function createContainer();

}
