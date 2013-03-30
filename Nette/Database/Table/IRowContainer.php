<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Database\Table;

use Nette\Database;



/**
 * Container of database result fetched into ActiveRow objects.
 *
 * @author     Jan Skrasek
 *
 * @method     IRow|bool  fetch() Fetches single row object.
 * @method     IRow[]     fetchAll() Fetches all rows.
 */
interface IRowContainer extends Database\IRowContainer
{
}
