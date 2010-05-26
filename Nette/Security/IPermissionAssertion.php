<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Security
 */

namespace Nette\Security;

use Nette;



/**
 * Represents conditional ACL Rules with Assertions.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
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
