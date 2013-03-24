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



/**
 * Object capable of returning native Nette Database ActiveRow
 */
interface IActiveRowAccessor
{

	/**
	 * Returns ActiveRow
	 * @return ActiveRow
	 */
	function getActiveRow();

}
