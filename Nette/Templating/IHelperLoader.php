<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Templating;



/**
 * Interface for helper loading class
 *
 * @author Filip Halaxa <filip@halaxa.cz>
 */
interface IHelperLoader
{

	/**
	 * Try to load the requested helper.
	 * @param  string  helper name
	 * @return callable
	 */
	public function loadHelper($helper);

}
