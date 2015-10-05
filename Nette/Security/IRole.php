<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Security;

use Nette;


/**
 * Represents role, an object that may request access to an IResource.
 *
 * @author     David Grudl
 */
interface IRole
{

	/**
	 * Returns a string identifier of the Role.
	 * @return string
	 */
	function getRoleId();

}
