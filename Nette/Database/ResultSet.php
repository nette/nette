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
 * Represents a result set.
 *
 * @author     David Grudl
 * @author     Jan Skrasek
 *
 * @property-read Connection $connection
 */
class ResultSet extends Nette\Object implements \Iterator, IRowContainer
{
	/** @var Connection */
	private $connection;

	/** @var \PDOStatement|NULL */
	private $pdoStatement;

	/** @var IRow */
	private $result;

	/** @var int */
	private $resultKey = -1;

	/** @var IRow[] */
	private $results;

	/** @var float */
	private $time;

	/** @var string */
	private $queryString;

	/** @var array */
	private $params;

	/** @var array */
	private $types;



	public function __construct(Connection $connection, $queryString, array $params)
	{
		$time = microtime(TRUE);
		$this->connection = $connection;
		$this->queryString = $queryString;
		$this->params = $params;

		if (substr($queryString, 0, 2) === '::') {
			$connection->getPdo()->{substr($queryString, 2)}();
		} elseif ($queryString !== NULL) {
			$this->pdoStatement = $connection->getPdo()->prepare($queryString);
			$this->pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
			$this->pdoStatement->execute($params);
		}
		$this->time = microtime(TRUE) - $time;
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
		return $this->queryString;
	}



	/**
	 * @return array
	 */
	public function getParameters()
	{
		return $this->params;
	}



	/**
	 * @return int
	 */
	public function getColumnCount()
	{
		return $this->pdoStatement ? $this->pdoStatement->columnCount() : NULL;
	}



	/**
	 * @return int
	 */
	public function getRowCount()
	{
		return $this->pdoStatement ? $this->pdoStatement->rowCount() : NULL;
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
		if ($this->types === NULL) {
			$this->types = (array) $this->connection->getSupplementalDriver()->getColumnTypes($this->pdoStatement);
		}

		foreach ($this->types as $key => $type) {
			$value = $row[$key];
			if ($value === NULL || $value === FALSE || $type === IReflection::FIELD_TEXT) {

			} elseif ($type === IReflection::FIELD_INTEGER) {
				$row[$key] = is_float($tmp = $value * 1) ? $value : $tmp;

			} elseif ($type === IReflection::FIELD_FLOAT) {
				if (($pos = strpos($value, '.')) !== FALSE) {
					$value = rtrim(rtrim($pos === 0 ? "0$value" : $value, '0'), '.');
				}
				$float = (float) $value;
				$row[$key] = (string) $float === $value ? $float : $value;

			} elseif ($type === IReflection::FIELD_BOOL) {
				$row[$key] = ((bool) $value) && $value !== 'f' && $value !== 'F';

			} elseif ($type === IReflection::FIELD_DATETIME || $type === IReflection::FIELD_DATE || $type === IReflection::FIELD_TIME) {
				$row[$key] = new Nette\DateTime($value);

			} elseif ($type === IReflection::FIELD_UNIX_TIMESTAMP) {
				$row[$key] = Nette\DateTime::from($value);
			}
		}

		return $this->connection->getSupplementalDriver()->normalizeRow($row);
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
			throw new Nette\InvalidStateException('Nette\\Database\\ResultSet implements only one way iterator.');
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
		$data = $this->pdoStatement ? $this->pdoStatement->fetch() : NULL;
		if (!$data) {
			$this->pdoStatement->closeCursor();
			return FALSE;
		}

		$row = new Row;
		foreach ($this->normalizeRow($data) as $key => $value) {
			$row->$key = $value;
		}

		if ($this->result === NULL && count($data) !== $this->pdoStatement->columnCount()) {
			trigger_error('Found duplicate columns in database result set.', E_USER_NOTICE);
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
