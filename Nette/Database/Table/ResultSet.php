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

use Nette,
	Nette\Caching\Cache,
	Nette\Caching\IStorage,
	Nette\Database,
	Nette\Database\Connection,
	Nette\Database\IReflection;



/**
 * Table row result set.
 *
 * @author     Jan Skrasek
 */
class ResultSet extends BaseResultSet
{

	/** @var Database\ResultSet */
	protected $resultSet;



	/**
	 * @param  string
	 * @param  Database\ResultSet
	 * @param  Connection
	 * @param  IReflection
	 * @param  IStorage
	 */
	public function __construct($table, Database\ResultSet $resultSet, Connection $connection, IReflection $reflection, IStorage $cacheStorage = NULL)
	{
		parent::__construct($table, $connection, $reflection, $cacheStorage);
		$this->resultSet = $resultSet;
	}



	/**
	 * @return string
	 */
	public function getSql()
	{
		return $this->resultSet->getQueryString();
	}



	/********************* internal ****************d*g**/



	protected function execute()
	{
		if ($this->rows !== NULL) {
			return;
		}


		while ($row = $this->resultSet->fetch()) {
			$row = new Row((array) $row, $this);
			if ($signature = $row->getSignature(FALSE)) {
				$this->rows[$signature] = $row;
			} else {
				$this->rows[] = $row;
			}
		}

		$this->data = $this->rows;
	}



	protected function getSpecificCacheKey()
	{
		return spl_object_hash($this);
	}

}
