<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette
 */

namespace Nette;

use Nette;



/**
 * The service locator.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette
 */
interface IServiceLocator
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
