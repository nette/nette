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
interface IContext
{

	/**
	 * Adds the specified service to the service container.
	 * @param  string service name
	 * @param  mixed  object, class name or factory callback
	 * @param  bool   is singleton?
	 * @param  array  factory options
	 * @return void
	 */
	function addService($name, $service, $singleton = TRUE, array $options = NULL);

	/**
	 * Gets the service object of the specified type.
	 * @param  string service name
	 * @param  array  options in case service is not singleton
	 * @return mixed
	 */
	function getService($name, array $options = NULL);

	/**
	 * Removes the specified service type from the service container.
	 * @return void
	 */
	function removeService($name);

	/**
	 * Exists the service?
	 * @return bool
	 */
	function hasService($name);

}
