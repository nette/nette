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
 * Authorizator checks if a given role has authorization
 * to access a given resource.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Security
 */
interface IAuthorizator
{
	/** Set type: all */
	const ALL = NULL;

	/** Permission type: allow */
	const ALLOW = TRUE;

	/** Permission type: deny */
	const DENY = FALSE;


	/**
	 * Performs a role-based authorization.
	 * @param  string  role
	 * @param  string  resource
	 * @param  string  privilege
	 * @return bool
	 */
	function isAllowed($role/*5.2* = self::ALL*/, $resource/*5.2* = self::ALL*/, $privilege/*5.2* = self::ALL*/);

}
