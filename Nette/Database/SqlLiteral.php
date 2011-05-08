<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Database;

use Nette;



/**
 * SQL literal value.
 *
 * @author     Jakub Vrana
 */
class SqlLiteral
{
	/** @var string */
	public $value = '';


	public function __construct($value)
	{
		$this->value = (string) $value;
	}

}
