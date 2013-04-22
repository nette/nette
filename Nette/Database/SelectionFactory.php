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
 * Table\Selection factory.
 *
 * @author     David Grudl
 */
class SelectionFactory extends Nette\Object
{
	/** @var Connection */
	private $connection;

	/** @var IReflection */
	private $reflection;

	/** @var Nette\Caching\IStorage */
	private $cacheStorage;


	public function __construct(Connection $connection, IReflection $reflection = NULL, Nette\Caching\IStorage $cacheStorage = NULL)
	{
		$this->connection = $connection;
		$this->reflection = $reflection ?: new Reflection\ConventionalReflection;
		$this->cacheStorage = $cacheStorage;
	}



	/** @return Nette\Database\Table\Selection */
	public function table($table)
	{
		return new Table\Selection($this->connection, $table, $this->reflection, $this->cacheStorage);
	}

}
