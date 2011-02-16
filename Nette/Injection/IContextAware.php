<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette;

use Nette;



/**
 * Object to which can be set context.
 *
 * @author     Jan Marek
 */
interface IContextAware
{

	/**
	 * Sets service container.
	 * @param  IContext service container
	 * @return void
	 */
	function setContext(IContext $context);

}
