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
 * @package    Nette\Security
 * @version    $Id$
 */

/*namespace Nette\Security;*/



/**
 * Represents conditional ACL Rules with Assertions.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Security
 */
interface IPermissionAssertion
{
	/**
	 * Returns true if and only if the assertion conditions are met.
	 *
	 * This method is passed the ACL, Role, Resource, and privilege to which the authorization query applies. If the
	 * $role, $resource, or $privilege parameters are Permission::ALL, it means that the query applies to all Roles,
	 * Resources, or privileges, respectively.
	 *
	 * @param  Permission
	 * @param  string  role
	 * @param  string  resource
	 * @param  string|NULL  privilege
	 * @return bool
	 */
	public function assert(Permission $acl, $roleId, $resourceId, $privilege);
}
