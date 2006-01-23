<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com/
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com/
 * @category   Nette
 * @package    Nette
 */

/*namespace Nette;*/



/**
 * The service locator (EXPERIMENTAL).
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette
 * @version    $Revision$ $Date$
 */
interface IServiceLocator
{

    /**
     * Adds the specified service to the service container.
     */
    function addService($service, $promote = FALSE);

    /**
     * Removes the specified service type from the service container.
     */
    function removeService($type, $promote = FALSE);

    /**
     * Gets the service object of the specified type.
     */
    function getService($type);

    /**
     * Returns the container if any.
     * @return IServiceLocator|NULL
     */
    function getParent();
}
