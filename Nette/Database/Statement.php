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

use Nette,
	PDO,
	Nette\ObjectMixin;



/**
 * Represents a prepared statement / result set.
 *
 * @author     David Grudl
 *
 * @property-read Connection $connection
 * @property-write $fetchMode
 */
class Statement extends Nette\Object implements IRowContainer
{
	/** @var Connection */
	private $connection;

	/** @var \PDOStatement */
	private $statement;

	/** @var array */
	private $result = array();

	/** @var float */
	private $time;

	/** @var array */
	private $types;



	public function __construct(Connection $connection, $sqlQuery, array $params)
	{
		$this->connection = $connection;
		$this->statement = $connection->prepare($sqlQuery);
		$this->statement->setFetchMode(PDO::FETCH_ASSOC);
		$this->execute($params);
	}



	/**
	 * @return Connection
	 */
	public function getConnection()
	{
		return $this->connection;
	}



	/**
	 * @return \PDOStatement
	 */
	public function getStatement()
	{
		return $this->statement;
	}



	/**
	 * @return string
	 */
	public function getQueryString()
	{
		return $this->statement->queryString;
	}



	/**
	 * @return int
	 */
	public function columnCount()
	{
		return $this->statement->columnCount();
	}



	/**
	 * @return int
	 */
	public function rowCount()
	{
		return $this->statement->rowCount();
	}



	/**
	 * @return float
	 */
	public function getTime()
	{
		return $this->time;
	}



	/**
	 * Normalizes result row.
	 * @param  array
	 * @return array
	 */
	public function normalizeRow($row)
	{
		foreach ($this->detectColumnTypes() as $key => $type) {
			$value = $row[$key];
			if ($value === NULL || $value === FALSE || $type === IReflection::FIELD_TEXT) {

			} elseif ($type === IReflection::FIELD_INTEGER) {
				$row[$key] = is_float($tmp = $value * 1) ? $value : $tmp;

			} elseif ($type === IReflection::FIELD_FLOAT) {
				$row[$key] = (string) ($tmp = (float) $value) === $value ? $tmp : $value;

			} elseif ($type === IReflection::FIELD_BOOL) {
				$row[$key] = ((bool) $value) && $value !== 'f' && $value !== 'F';

			} elseif ($type === IReflection::FIELD_DATETIME || $type === IReflection::FIELD_DATE || $type === IReflection::FIELD_TIME) {
				$row[$key] = new Nette\DateTime($value);

			}
		}

		return $this->connection->getSupplementalDriver()->normalizeRow($row, $this);
	}



	private function detectColumnTypes()
	{
		if ($this->types === NULL) {
			$this->types = array();
			if ($this->connection->getSupplementalDriver()->isSupported(ISupplementalDriver::SUPPORT_COLUMNS_META)) { // workaround for PHP bugs #53782, #54695
				$col = 0;
				while ($meta = $this->statement->getColumnMeta($col++)) {
					if (isset($meta['native_type'])) {
						$this->types[$meta['name']] = Helpers::detectType($meta['native_type']);
					}
				}
			}
		}
		return $this->types;
	}



	/**
	 * Executes statement.
	 */
	private function execute(array $params)
	{
		$time = microtime(TRUE);
		try {
			$this->statement->execute($params);
		} catch (\PDOException $e) {
			$e->queryString = $this->queryString;
			throw $e;
		}
		$this->time = microtime(TRUE) - $time;
		$this->connection->__call('onQuery', array($this, $params)); // $this->connection->onQuery() in PHP 5.3
	}



	/********************* misc tools ****************d*g**/



	/**
	 * Displays complete result set as HTML table for debug purposes.
	 * @return void
	 */
	public function dump()
	{
		Helpers::dumpResult($this);
	}



	/********************* interface Iterator ****************d*g**/



	public function rewind()
	{
		reset($this->result);
	}



	public function current()
	{
		return current($this->result);
	}



	public function key()
	{
		return key($this->result);
	}



	public function next()
	{
		next($this->result);
	}



	public function valid()
	{
		if (current($this->result) !== FALSE)
			return TRUE;

		return $this->fetch() !== FALSE;
	}



	/********************* interface IRowContainer ****************d*g**/



	public function fetch()
	{
		$data = $this->statement->fetch();
		if (!$data) {
			return FALSE;
		}

		$row = new Row;
		foreach ($this->normalizeRow($data) as $key => $value) {
			$row[$key] = $value;
		}

		return $this->result[] = $row;
	}



	public function fetchPairs($key, $value = NULL)
	{
		$return = array();
		foreach ($this as $row) {
			$return[is_object($row[$key]) ? (string) $row[$key] : $row[$key]] = ($value ? $row[$value] : $row);
		}
		return $return;
	}

}
