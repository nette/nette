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
 * @property       IReflection          $databaseReflection
 * @property-read  ISupplementalDriver  $supplementalDriver
 * @property-read  string               $dsn
 */
class Connection extends PDO
{
	/** @var string */
	private $dsn;

	/** @var ISupplementalDriver */
	private $driver;

	/** @var SqlPreprocessor */
	private $preprocessor;

	/** @var IReflection */
	private $databaseReflection;

	/** @var Nette\Caching\Cache */
	private $cache;

	/** @var array of function(Statement $result, $params); Occurs after query is executed */
	public $onQuery;

	/** @var array */
	private $args;



	public function __construct($dsn, $user = NULL, $password  = NULL, array $options = NULL, $driverClass = NULL)
	{
		$this->args = array($dsn, $user, $password, $options, $driverClass);
	}



	public function init()
	{
		if (!$this->isInitialized()) {
			$driverClass = $this->args[4];
			parent::__construct($this->dsn = $this->args[0], $this->args[1], $this->args[2], $options = $this->args[3]);
			$this->args = NULL;
			$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('Nette\Database\Statement', array($this)));

			$driverClass = $driverClass ?: 'Nette\Database\Drivers\\' . ucfirst(str_replace('sql', 'Sql', $this->getAttribute(PDO::ATTR_DRIVER_NAME))) . 'Driver';
			$this->driver = new $driverClass($this, (array) $options);
			$this->preprocessor = new SqlPreprocessor($this);
		}
	}



	/** @return bool */
	public function isInitialized()
	{
		return $this->args === NULL;
	}



	public function getDsn()
	{
		return $this->dsn;
	}



	/** @return ISupplementalDriver */
	public function getSupplementalDriver()
	{
		return $this->driver;
	}



	/**
	 * Sets database reflection.
	 * @return Connection   provides a fluent interface
	 */
	public function setDatabaseReflection(IReflection $databaseReflection)
	{
		$databaseReflection->setConnection($this);
		$this->databaseReflection = $databaseReflection;
		return $this;
	}



	/** @return IReflection */
	public function getDatabaseReflection()
	{
		if (!$this->databaseReflection) {
			$this->setDatabaseReflection(new Reflection\ConventionalReflection);
		}
		return $this->databaseReflection;
	}



	/**
	 * Sets cache storage engine.
	 * @return Connection   provides a fluent interface
	 */
	public function setCacheStorage(Nette\Caching\IStorage $storage = NULL)
	{
		$this->cache = $storage ? new Nette\Caching\Cache($storage, 'Nette.Database.' . md5($this->dsn)) : NULL;
		return $this;
	}



	public function getCache()
	{
		return $this->cache;
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



	public function beginTransaction()
	{
		$this->init();
		return parent::beginTransaction();
	}



	public function getAttribute($attribute)
	{
		$this->init();
		return parent::getAttribute($attribute);
	}



	/**
	 * Generates and executes SQL query.
	 * @param  string  statement
	 * @param  mixed   [parameters, ...]
	 * @return int     number of affected rows
	 */
	public function exec($statement)
	{
		$this->init();
		$args = func_get_args();
		return $this->queryArgs(array_shift($args), $args)->rowCount();
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
		return $this->prepare($statement)->execute($params);
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



	/********************* selector ****************d*g**/



	/**
	 * Creates selector for table.
	 * @param  string
	 * @return Nette\Database\Table\Selection
	 */
	public function table($table)
	{
		$this->init();
		return new Table\Selection($this, $table);
	}



	/********************* Nette\Object behaviour ****************d*g**/



	/**
	 * @return Nette\Reflection\ClassType
	 */
	public /**/static/**/ function getReflection()
	{
		return new Nette\Reflection\ClassType(/*5.2*$this*//**/get_called_class()/**/);
	}



	public function __call($name, $args)
	{
		return ObjectMixin::call($this, $name, $args);
	}



	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}



	public function __set($name, $value)
	{
		return ObjectMixin::set($this, $name, $value);
	}



	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}



	public function __unset($name)
	{
		ObjectMixin::remove($this, $name);
	}

}
