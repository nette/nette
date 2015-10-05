<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
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
