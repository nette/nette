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
 * Represents the user of application.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Security
 */
interface IIdentity
{

	/**
	 * Returns the name of user.
	 * @return string
	 */
	//function getName();

	/**
	 * Returns a list of roles that the user is a member of.
	 * @return array
	 */
	function getRoles();

}
