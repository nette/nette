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
	/** @var string */
	private $dsn;

	/** @var array */
	private $login;

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

	/** @var array of function(Statement $result, $params); Occurs after query is executed */
	public $onQuery;



	public function __construct($dsn, $user = NULL, $password = NULL, array $options = NULL)
	{
		if (func_num_args() > 4) { // compatiblity
			$options['driverClass'] = func_get_arg(4);
		}
		$this->dsn = $dsn;
		$this->login = array($user, $password);
		$this->options = (array) $options;

		if (empty($options['lazy'])) {
			$this->connect();
		}
	}



	private function connect()
	{
		$this->pdo = new PDO($this->dsn, $this->login[0], $this->login[1], $this->options);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$driverClass = empty($this->options['driverClass'])
			? 'Nette\Database\Drivers\\' . ucfirst(str_replace('sql', 'Sql', $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME))) . 'Driver'
			: $this->options['driverClass'];
		$this->driver = new $driverClass($this, $this->options);
		$this->preprocessor = new SqlPreprocessor($this);
	}



	/** @return string */
	public function getDsn()
	{
		return $this->dsn;
	}



	/** @return PDO */
	public function getPdo()
	{
		if (!$this->pdo) {
			$this->connect();
		}
		return $this->pdo;
	}



	/** @return ISupplementalDriver */
	public function getSupplementalDriver()
	{
		if (!$this->pdo) {
			$this->connect();
		}
		return $this->driver;
	}



	/** @return bool */
	public function beginTransaction()
	{
		return $this->getPdo()->beginTransaction();
	}



	/** @return bool */
	public function commit()
	{
		return $this->getPdo()->commit();
	}



	/** @return bool */
	public function rollBack()
	{
		return $this->getPdo()->rollBack();
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
	 * @return Statement
	 */
	public function query($statement)
	{
		$args = func_get_args();
		return $this->queryArgs(array_shift($args), $args);
	}



	/**
	 * @param  string  statement
	 * @param  array
	 * @return Statement
	 */
	public function queryArgs($statement, array $params)
	{
		if (!$this->pdo) {
			$this->connect();
		}
		if ($params) {
			list($statement, $params) = $this->preprocessor->process($statement, $params);
		}

		return new Statement($this, $statement, $params);
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
