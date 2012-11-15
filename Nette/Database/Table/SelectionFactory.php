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

use Nette;



/**
 * Table\Selection factory.
 *
 * @author     David Grudl
 */
class SelectionFactory extends Nette\Object
{
	/** @var Nette\Database\Connection */
	private $connection;

	/** @var Nette\Database\IReflection */
	private $reflection;

	/** @var Nette\Caching\IStorage */
	private $cacheStorage;


	public function __construct(Nette\Database\Connection $connection, Nette\Database\IReflection $reflection = NULL, Nette\Caching\IStorage $cacheStorage = NULL)
	{
		$this->connection = $connection;
		$this->reflection = $reflection ?: new Nette\Database\Reflection\ConventionalReflection;
		$this->cacheStorage = $cacheStorage;
	}



	/** @return Selection */
	public function create($table)
	{
		return new Selection($this->connection, $table, $this->reflection, $this->cacheStorage);
	}

}
