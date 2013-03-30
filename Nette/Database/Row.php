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
 * Represents a single table row.
 *
 * @author     David Grudl
 * @author     Jan Skrasek
 */
class Row extends Nette\ArrayHash implements IRow
{

	/**
	 * Returns a item.
	 * @param  mixed  key or index
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		if (is_int($key)) {
			$arr = array_values((array) $this);
			return $arr[$key];
		}
		return $this->$key;
	}



	/**
	 * Checks if $key exists.
	 * @param  mixed  key or index
	 * @return bool
	 */
	public function offsetExists($key)
	{
		if (is_int($key)) {
			$arr = array_values((array) $this);
			return isset($arr[$key]);
		}
		return parent::offsetExists($key);
	}

}
