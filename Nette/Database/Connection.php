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
	Nette\ObjectMixin,
	PDO;



/**
 * Represents a connection between PHP and a database server.
 *
 * @author     David Grudl
 *
 * @property-read  ISupplementalDriver  $supplementalDriver
 * @property-read  string               $dsn
 * @property-read  PDO                  $pdo
 */
class Connection extends Nette\Object
{
	/** @var array */
	private $params;

	/** @var array */
	private $options;

	/** @var ISupplementalDriver */
	private $driver;

	/** @var SqlPreprocessor */
	private $preprocessor;

	/** @var Table\SelectionFactory */
	private $selectionFactory;

	/** @var PDO */
	private $pdo;

	/** @var array of function(Connection $connection, ResultSet|Exception $result); Occurs after query is executed */
	public $onQuery;



	public function __construct($dsn, $user = NULL, $password = NULL, array $options = NULL)
	{
		if (func_num_args() > 4) { // compatiblity
			$options['driverClass'] = func_get_arg(4);
		}
		$this->params = array($dsn, $user, $password);
		$this->options = (array) $options;

		if (empty($options['lazy'])) {
			$this->connect();
		}
	}



	private function connect()
	{
		if ($this->pdo) {
			return;
		}
		$this->pdo = new PDO($this->params[0], $this->params[1], $this->params[2], $this->options);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$class = empty($this->options['driverClass'])
			? 'Nette\Database\Drivers\\' . ucfirst(str_replace('sql', 'Sql', $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME))) . 'Driver'
			: $this->options['driverClass'];
		$this->driver = new $class($this, $this->options);
		$this->preprocessor = new SqlPreprocessor($this);
	}



	/** @return string */
	public function getDsn()
	{
		return $this->params[0];
	}



	/** @return PDO */
	public function getPdo()
	{
		$this->connect();
		return $this->pdo;
	}



	/** @return ISupplementalDriver */
	public function getSupplementalDriver()
	{
		$this->connect();
		return $this->driver;
	}



	/** @return void */
	public function beginTransaction()
	{
		$this->queryArgs('::beginTransaction', array());
	}



	/** @return void */
	public function commit()
	{
		$this->queryArgs('::commit', array());
	}



	/** @return void */
	public function rollBack()
	{
		$this->queryArgs('::rollBack', array());
	}



	/**
	 * @param  string  sequence object
	 * @return string
	 */
	public function getInsertId($name = NULL)
	{
		return $this->getPdo()->lastInsertId($name);
	}



	/**
	 * @param  string  string to be quoted
	 * @param  int     data type hint
	 * @return string
	 */
	public function quote($string, $type = PDO::PARAM_STR)
	{
		return $this->getPdo()->quote($string, $type);
	}



	/**
	 * Generates and executes SQL query.
	 * @param  string  statement
	 * @param  mixed   [parameters, ...]
	 * @return ResultSet
	 */
	public function query($statement)
	{
		$args = func_get_args();
		return $this->queryArgs(array_shift($args), $args);
	}



	/**
	 * @param  string  statement
	 * @param  array
	 * @return ResultSet
	 */
	public function queryArgs($statement, array $params)
	{
		$this->connect();
		if ($params) {
			array_unshift($params, $statement);
			list($statement, $params) = $this->preprocessor->process($params);
		}

		try {
			$result = new ResultSet($this, $statement, $params);
		} catch (\PDOException $e) {
			$e->queryString = $statement;
			$this->onQuery($this, $e);
			throw $e;
		}
		$this->onQuery($this, $result);
		return $result;
	}



	/********************* shortcuts ****************d*g**/



	/**
	 * Shortcut for query()->fetch()
	 * @param  string  statement
	 * @param  mixed   [parameters, ...]
	 * @return Row
	 */
	public function fetch($args)
	{
		$args = func_get_args();
		return $this->queryArgs(array_shift($args), $args)->fetch();
	}



	/**
	 * Shortcut for query()->fetchField()
	 * @param  string  statement
	 * @param  mixed   [parameters, ...]
	 * @return mixed
	 */
	public function fetchField($args)
	{
		$args = func_get_args();
		return $this->queryArgs(array_shift($args), $args)->fetchField();
	}



	/**
	 * Shortcut for query()->fetchPairs()
	 * @param  string  statement
	 * @param  mixed   [parameters, ...]
	 * @return array
	 */
	public function fetchPairs($args)
	{
		$args = func_get_args();
		return $this->queryArgs(array_shift($args), $args)->fetchPairs(0, 1);
	}



	/**
	 * Shortcut for query()->fetchAll()
	 * @param  string  statement
	 * @param  mixed   [parameters, ...]
	 * @return array
	 */
	public function fetchAll($args)
	{
		$args = func_get_args();
		return $this->queryArgs(array_shift($args), $args)->fetchAll();
	}



	/**
	 * @return SqlLiteral
	 */
	public static function literal($value)
	{
		$args = func_get_args();
		return new SqlLiteral(array_shift($args), $args);
	}



	/********************* Selection ****************d*g**/



	/**
	 * Creates selector for table.
	 * @param  string
	 * @return Nette\Database\Table\Selection
	 */
	public function table($table)
	{
		if (!$this->selectionFactory) {
			$this->selectionFactory = new Table\SelectionFactory($this);
		}
		return $this->selectionFactory->create($table);
	}



	/**
	 * @return Connection   provides a fluent interface
	 */
	public function setSelectionFactory(Table\SelectionFactory $selectionFactory)
	{
		$this->selectionFactory = $selectionFactory;
		return $this;
	}



	/** @deprecated */
	function setDatabaseReflection()
	{
		trigger_error(__METHOD__ . '() is deprecated; use setSelectionFactory() instead.', E_USER_DEPRECATED);
		return $this;
	}



	/** @deprecated */
	function setCacheStorage()
	{
		trigger_error(__METHOD__ . '() is deprecated; use setSelectionFactory() instead.', E_USER_DEPRECATED);
	}



	/** @deprecated */
	function lastInsertId($name = NULL)
	{
		trigger_error(__METHOD__ . '() is deprecated; use getInsertId() instead.', E_USER_DEPRECATED);
		return $this->getInsertId($name);
	}


	/** @deprecated */
	function exec($statement)
	{
		trigger_error(__METHOD__ . '() is deprecated; use query()->getRowCount() instead.', E_USER_DEPRECATED);
		$args = func_get_args();
		return $this->queryArgs(array_shift($args), $args)->getRowCount();
	}

}
