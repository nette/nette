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

use Nette;



/**
 * Information about tables and columns structure.
 */
interface IRowFactory
{
	/**
	 * Create new entity
	 *
	 * @param  array
	 * @param  Table\Selection
	 * @return mixed
	 */
	function createRow(array $data, Table\Selection $table);
}
