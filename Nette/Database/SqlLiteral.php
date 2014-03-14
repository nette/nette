<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
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
	private $value;

	/** @var array */
	private $parameters;


	public function __construct($value, array $parameters = array())
	{
		$this->value = (string) $value;
		$this->parameters = $parameters;
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
