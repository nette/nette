<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette
 */

/*namespace Nette;*/



/**
 * The service locator.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
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
