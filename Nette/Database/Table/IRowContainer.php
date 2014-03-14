<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Database\Table;

use Nette\Database;


/**
 * Container of database result fetched into IRow objects.
 *
 * @author     Jan Skrasek
 *
 * @method     IRow|bool  fetch() Fetches single row object.
 * @method     IRow[]     fetchAll() Fetches all rows.
 */
interface IRowContainer extends Database\IRowContainer
{
}
