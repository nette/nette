<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Database;



/**
 * Container of database result fetched into ActiveRow objects.
 *
 * @author     Jan Skrasek
 */
interface IActiveRowContainer extends IRowContainer
{

	/**
	 * Fetchs single row object
	 * @return Table\ActiveRow or FALSE if there is no row
	 */
	function fetch();

}
