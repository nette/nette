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
	/** @var array of function(Connection $connection); Occurs after connection is established */
	public $onConnect;

	/** @var array of function(Connection $connection, ResultSet|Exception $result); Occurs after query is executed */
	public $onQuery;

	/** @var array */
	private $params;

	/** @var array */
	private $options;

	/** @var ISupplementalDriver */
	private $driver;

	/** @var SqlPreprocessor */
	private $preprocessor;

	/** @var Container */
	private $container;

	/** @var PDO */
	private $pdo;


	public function __construct($dsn, $user = NULL, $password = NULL, array $options = NULL)
	{
		if (func_num_args() > 4) { // compatibility
			$options['driverClass'] = func_get_arg(4);
		}
		$this->params = array($dsn, $user, $password);
		$this->options = (array) $options;

		if (empty($options['lazy'])) {
			$this->connect();
		}
	}


	public function connect()
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
		$this->onConnect($this);
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


	/** @deprecated */
	function beginTransaction()
	{
		$this->queryArgs('::beginTransaction', array());
	}


	/** @deprecated */
	function commit()
	{
		$this->queryArgs('::commit', array());
	}


	/** @deprecated */
	public function rollBack()
	{
		$this->queryArgs('::rollBack', array());
	}


	/** @deprecated */
	public function query($statement)
	{
		trigger_error(__METHOD__ . '() is deprecated; use Database\Container::query() instead.', E_USER_DEPRECATED);
		$args = func_get_args();
		return $this->queryArgs(array_shift($args), $args);
	}


	/** @deprecated */
	function queryArgs($statement, array $params)
	{
		trigger_error(__METHOD__ . '() is deprecated; use Database\Container::queryArgs() instead.', E_USER_DEPRECATED);
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


	/** @deprecated */
	function fetch($args)
	{
		trigger_error(__METHOD__ . '() is deprecated; use Database\Container::' . __METHOD__ . '() instead.', E_USER_DEPRECATED);
		$args = func_get_args();
		return $this->queryArgs(array_shift($args), $args)->fetch();
	}


	/** @deprecated */
	function fetchField($args)
	{
		trigger_error(__METHOD__ . '() is deprecated; use Database\Container::' . __METHOD__ . '() instead.', E_USER_DEPRECATED);
		$args = func_get_args();
		return $this->queryArgs(array_shift($args), $args)->fetchField();
	}


	/** @deprecated */
	function fetchPairs($args)
	{
		trigger_error(__METHOD__ . '() is deprecated; use Database\Container::' . __METHOD__ . '() instead.', E_USER_DEPRECATED);
		$args = func_get_args();
		return $this->queryArgs(array_shift($args), $args)->fetchPairs(0, 1);
	}


	/** @deprecated */
	function fetchAll($args)
	{
		trigger_error(__METHOD__ . '() is deprecated; use Database\Container::' . __METHOD__ . '() instead.', E_USER_DEPRECATED);
		$args = func_get_args();
		return $this->queryArgs(array_shift($args), $args)->fetchAll();
	}


	/** @deprecated */
	static function literal($value)
	{
		trigger_error(__METHOD__ . '() is deprecated; use Database\Container::' . __METHOD__ . '() instead.', E_USER_DEPRECATED);
		return Container::literal($value);
	}


	/********************* Selection ****************d*g**/


	/** @deprecated */
	function table($table)
	{
		trigger_error(__METHOD__ . '() is deprecated; use Database\Container::' . __METHOD__ . '() instead.', E_USER_DEPRECATED);
		if (!$this->container) {
			$this->container = new Container($this);
		}
		return $this->container->table($table);
	}


	/** @deprecated */
	function setContainer(Container $container)
	{
		$this->container = $container;
		return $this;
	}


	/** @deprecated */
	function getContainer()
	{
		return $this->container;
	}


	/** @deprecated */
	function setDatabaseReflection()
	{
		trigger_error(__METHOD__ . '() is deprecated; use Database\Container instead.', E_USER_DEPRECATED);
		return $this;
	}


	/** @deprecated */
	function setCacheStorage()
	{
		trigger_error(__METHOD__ . '() is deprecated; use Database\Container instead.', E_USER_DEPRECATED);
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
		trigger_error(__METHOD__ . '() is deprecated; use Database\Container::query()->getRowCount() instead.', E_USER_DEPRECATED);
		$args = func_get_args();
		return $this->queryArgs(array_shift($args), $args)->getRowCount();
	}

}
