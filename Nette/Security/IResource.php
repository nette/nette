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
 * Represents resource, an object to which access is controlled.
 *
 * @author     David Grudl
 */
interface IResource
{

	/**
	 * Returns a string identifier of the Resource.
	 * @return string
	 */
	public function getResourceId();

}
