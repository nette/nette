<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Security;

use Nette;



/**
 * Represents conditional ACL Rules with Assertions.
 *
 * @author     David Grudl
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
