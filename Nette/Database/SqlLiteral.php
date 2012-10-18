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
 * SQL literal value.
 *
 * @author     Jakub Vrana
 */
class SqlLiteral extends Nette\Object
{
	/** @var bool Whether literal can be delimited */
	public $delimitable = false;

	/** @var bool Whether sql builder can examine literal for possible table joins */
	public $examinable = false;

	/** @var string */
	private $value = '';



	public function __construct($value)
	{
		$this->value = (string) $value;
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->value;
	}

}
