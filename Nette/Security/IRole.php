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

/*namespace Nette\Security;*/



/**
 * Represents role, an object that may request access to an IResource.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Security
 */
interface IRole
{

	/**
	 * Returns a string identifier of the Role.
	 * @return string
	 */
	public function getRoleId();

}
