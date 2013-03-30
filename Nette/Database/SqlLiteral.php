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
 * @author     Jan Skrasek
 */
class SqlLiteral extends Nette\Object
{
	/** @var string */
	private $value = '';

	/** @var array */
	private $parameters = array();



	public function __construct($value)
	{
		$this->value = (string) $value;
		$this->parameters = array_slice(func_get_args(), 1);
	}



	/**
	 * @return array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->value;
	}

}
