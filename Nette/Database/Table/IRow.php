<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Database\Table;

use Nette\Database;


/**
 * Row interface.
 *
 * @author     Jan Skrasek
 */
interface IRow extends Database\IRow
{

	function setTable(Selection $name);

	function getTable();

	function getPrimary($need = TRUE);

	function getSignature($need = TRUE);

	function related($key, $throughColumn = NULL);

	function ref($key, $throughColumn = NULL);

}
