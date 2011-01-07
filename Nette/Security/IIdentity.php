<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Security;

use Nette;



/**
 * Represents the user of application.
 *
 * @author     David Grudl
 */
interface IIdentity
{

	/**
	 * Returns the ID of user.
	 * @return mixed
	 */
	function getId();

	/**
	 * Returns a list of roles that the user is a member of.
	 * @return array
	 */
	function getRoles();

}
