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



	public function __construct($dsn, $user = NULL, $password = NULL, array $options = NULL, $driverClass = NULL)
	{
		$this->pdo = $pdo = new PDO($this->dsn = $dsn, $user, $password, $options);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$driverClass = $driverClass ?: 'Nette\Database\Drivers\\' . ucfirst(str_replace('sql', 'Sql', $pdo->getAttribute(PDO::ATTR_DRIVER_NAME))) . 'Driver';
		$this->driver = new $driverClass($this, (array) $options);
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
		return $this->pdo;
	}



	/** @return ISupplementalDriver */
	public function getSupplementalDriver()
	{
		return $this->driver;
	}



	/** @return bool */
	public function beginTransaction()
	{
		return $this->pdo->beginTransaction();
	}



	/** @return bool */
	public function commit()
	{
		return $this->pdo->commit();
	}



	/** @return bool */
	public function rollBack()
	{
		return $this->pdo->rollBack();
	}



	/**
	 * @param  string  sequence object
	 * @return string
	 */
	public function getInsertId($name = NULL)
	{
		return $this->pdo->lastInsertId($name);
	}



	/**
	 * @param  string  string to be quoted
	 * @param  int     data type hint
	 * @return string
	 */
	public function quote($string, $type = PDO::PARAM_STR)
	{
		return $this->pdo->quote($string, $type);
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
	 * Shortcut for query()->fetchColumn()
	 * @param  string  statement
	 * @param  mixed   [parameters, ...]
	 * @return mixed
	 */
	public function fetchColumn($args)
	{
		$args = func_get_args();
		return $this->queryArgs(array_shift($args), $args)->fetchColumn();
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
		return $this->queryArgs(array_shift($args), $args)->fetchPairs();
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
		trigger_error(__METHOD__ . '() is deprecated; use query()->rowCount() instead.', E_USER_DEPRECATED);
		$args = func_get_args();
		return $this->queryArgs(array_shift($args), $args)->rowCount();
	}

}
