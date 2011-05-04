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
 * The dependency injection container.
 *
 * @author     David Grudl
 */
interface IContainer
{

	/**
	 * Adds the specified service or service factory to the container.
	 * @param  string
	 * @param  mixed  object, class name or callback
	 * @return void
	 */
	function addService($name, $service);

	/**
	 * Gets the service object of the specified type.
	 * @param  string service name
	 * @return mixed
	 */
	function getService($name);

	/**
	 * Removes the specified service type from the container.
	 * @return void
	 */
	function removeService($name);

	/**
	 * Exists the service?
	 * @return bool
	 */
	function hasService($name);

}
