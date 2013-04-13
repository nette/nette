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
 * @author     Jan Skrasek
 *
 * @property-read Connection $connection
 */
class Statement extends Nette\Object implements \Iterator, IRowContainer
{
	/** @var Connection */
	private $connection;

	/** @var \PDOStatement */
	private $pdoStatement;

	/** @var IRow */
	private $result;

	/** @var int */
	private $resultKey = -1;

	/** @var IRow[] */
	private $results;

	/** @var float */
	private $time;

	/** @var array */
	private $types;



	public function __construct(Connection $connection, $sqlQuery, array $params)
	{
		$this->connection = $connection;
		$this->pdoStatement = $connection->getPdo()->prepare($sqlQuery);
		$this->pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
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
	 * @internal
	 * @return \PDOStatement
	 */
	public function getPdoStatement()
	{
		return $this->pdoStatement;
	}



	/**
	 * @return string
	 */
	public function getQueryString()
	{
		return $this->pdoStatement->queryString;
	}



	/**
	 * @return int
	 */
	public function getColumnCount()
	{
		return $this->pdoStatement->columnCount();
	}



	/**
	 * @return int
	 */
	public function getRowCount()
	{
		return $this->pdoStatement->rowCount();
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
				$value = strpos($value, '.') === FALSE ? $value : rtrim(rtrim($value, '0'), '.');
				$float = (float) $value;
				$row[$key] = (string) $float === $value ? $float : $value;

			} elseif ($type === IReflection::FIELD_BOOL) {
				$row[$key] = ((bool) $value) && $value !== 'f' && $value !== 'F';

			} elseif ($type === IReflection::FIELD_DATETIME || $type === IReflection::FIELD_DATE || $type === IReflection::FIELD_TIME) {
				$row[$key] = new Nette\DateTime($value);

			}
		}

		return $this->connection->getSupplementalDriver()->normalizeRow($row);
	}



	private function detectColumnTypes()
	{
		if ($this->types === NULL) {
			$this->types = array();
			if ($this->connection->getSupplementalDriver()->isSupported(ISupplementalDriver::SUPPORT_COLUMNS_META)) { // workaround for PHP bugs #53782, #54695
				$count = $this->pdoStatement->columnCount();
				for ($col = 0; $col < $count; $col++) {
					$meta = $this->pdoStatement->getColumnMeta($col);
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
			$this->pdoStatement->execute($params);
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
		if ($this->result === FALSE) {
			throw new Nette\InvalidStateException('Nette\\Database\\Statement implements only one way iterator.');
		}
	}



	public function current()
	{
		return $this->result;
	}



	public function key()
	{
		return $this->resultKey;
	}



	public function next()
	{
		$this->result = FALSE;
	}



	public function valid()
	{
		if ($this->result) {
			return TRUE;
		}

		return $this->fetch() !== FALSE;
	}



	/********************* interface IRowContainer ****************d*g**/



	/**
	 * @inheritDoc
	 */
	public function fetch()
	{
		$data = $this->pdoStatement->fetch();
		if (!$data) {
			return FALSE;
		}

		$row = new Row;
		foreach ($this->normalizeRow($data) as $key => $value) {
			$row->$key = $value;
		}

		$this->resultKey++;
		return $this->result = $row;
	}



	/**
	 * Fetches single field.
	 * @return mixed|FALSE
	 */
	public function fetchField($column = 0)
	{
		$row = $this->fetch();
		return $row ? $row[$column] : FALSE;
	}



	/**
	 * @inheritDoc
	 */
	public function fetchPairs($key, $value = NULL)
	{
		$return = array();
		foreach ($this->fetchAll() as $row) {
			$return[is_object($row[$key]) ? (string) $row[$key] : $row[$key]] = ($value === NULL ? $row : $row[$value]);
		}
		return $return;
	}



	/**
	 * @inheritDoc
	 */
	public function fetchAll()
	{
		if ($this->results === NULL) {
			$this->results = iterator_to_array($this);
		}

		return $this->results;
	}



	/** @deprecated */
	function columnCount()
	{
		trigger_error(__METHOD__ . '() is deprecated; use getColumnCount() instead.', E_USER_DEPRECATED);
		return $this->getColumnCount();
	}



	/** @deprecated */
	function rowCount()
	{
		trigger_error(__METHOD__ . '() is deprecated; use getRowCount() instead.', E_USER_DEPRECATED);
		return $this->getRowCount();
	}

}
