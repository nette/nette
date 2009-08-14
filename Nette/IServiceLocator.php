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
 * The service locator (EXPERIMENTAL).
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette
 */
interface IServiceLocator
{

	/**
	 * Adds the specified service to the service container.
	 * @param  mixed  object, class name or service factory callback
	 * @param  string optional service name (for factories is not optional)
	 * @param  bool   promote to higher level?
	 * @return void
	 */
	function addService($service, $name = NULL, $promote = FALSE);

	/**
	 * Removes the specified service type from the service container.
	 * @param  bool   promote to higher level?
	 * @return void
	 */
	function removeService($name, $promote = TRUE);

	/**
	 * Gets the service object of the specified type.
	 * @param  string service name
	 * @return mixed
	 */
	function getService($name);

	/**
	 * Returns the parent container if any.
	 * @return IServiceLocator|NULL
	 */
	function getParent();
}
