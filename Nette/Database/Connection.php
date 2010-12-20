<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Database;

use Nette,
	Nette\ObjectMixin,
	PDO;



/**
 * Represents a connection between PHP and a database server.
 *
 * @author     David Grudl
 */
class Connection extends PDO
{
	/** @var ISupplementalDriver */
	private $driver;

	/** @var SqlPreprocessor */
	private $preprocessor;

	/** @var Nette\Database\Reflection\DatabaseReflection */
	public $databaseReflection;

	/** @var Nette\Caching\Cache */
	public $cache;

	/** @var array */
	public $substitutions = array();

	/** @var array of function(Statement $result, $params); Occurs after query is executed */
	public $onQuery;



	public function __construct($dsn, $username = NULL, $password  = NULL, array $options = NULL)
	{
		parent::__construct($dsn, $username, $password, $options);
		$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('Nette\Database\Statement', array($this)));

		$class = 'Nette\Database\Drivers\Pdo' . $this->getAttribute(PDO::ATTR_DRIVER_NAME) . 'Driver';
		if (class_exists($class)) {
			$this->driver = new $class($this, (array) $options);
		}

		$this->preprocessor = new SqlPreprocessor($this);

		$this->databaseReflection = new Nette\Database\Reflection\DatabaseReflection; // TODO

		if (!Nette\Debug::$productionMode) {
			Nette\Debug::addPanel($panel = new DatabasePanel($dsn));
			$this->onQuery[] = callback($panel, 'logQuery');
		}
	}



	/** @return ISupplementalDriver */
	public function getSupplementalDriver()
	{
		return $this->driver;
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
	 * Generates and executes SQL query.
	 * @param  string  statement
	 * @param  mixed   [parameters, ...]
	 * @return int     number of affected rows
	 */
	public function exec($statement)
	{
		$args = func_get_args();
		return $this->queryArgs(array_shift($args), $args)->rowCount();
	}



	/**
	 * @param  string  statement
	 * @param  array
	 * @return Statement
	 */
	public function queryArgs($statement, $params)
	{
		foreach ($params as $value) {
			if (is_array($value) || is_object($value)) {
				$need = TRUE; break;
			}
		}
		if (isset($need) || strpos(':', $statement) !== FALSE) {
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



	/********************* selector ****************d*g**/



	/**
	 * Creates selector for table.
	 * @param  string
	 * @return Nette\Database\Selector\TableSelection
	 */
	public function table($table)
	{
		return new Nette\Database\Selector\TableSelection($table, $this);
	}



	/********************* misc ****************d*g**/



	/**
	 * Import SQL dump from file - extreme fast.
	 * @param  string  filename
	 * @return int  count of commands
	 */
	public function loadFile()
	{
		@set_time_limit(0); // intentionally @

		$handle = @fopen($file, 'r'); // intentionally @
		if (!$handle) {
			throw new FileNotFoundException("Cannot open file '$file'.");
		}

		$count = 0;
		$sql = '';
		while (!feof($handle)) {
			$s = fgets($handle);
			$sql .= $s;
			if (substr(rtrim($s), -1) === ';') {
				parent::exec($sql); // native query without logging
				$sql = '';
				$count++;
			}
		}
		fclose($handle);
		return $count;
	}



	/**
	 * Returns syntax highlighted SQL command.
	 * @param  string
	 * @return string
	 */
	public static function highlightSql($sql)
	{
		static $keywords1 = 'SELECT|UPDATE|INSERT(?:\s+INTO)?|REPLACE(?:\s+INTO)?|DELETE|FROM|WHERE|HAVING|GROUP\s+BY|ORDER\s+BY|LIMIT|OFFSET|SET|VALUES|LEFT\s+JOIN|INNER\s+JOIN|TRUNCATE';
		static $keywords2 = 'ALL|DISTINCT|DISTINCTROW|AS|USING|ON|AND|OR|IN|IS|NOT|NULL|LIKE|TRUE|FALSE';

		// insert new lines
		$sql = " $sql ";
		$sql = preg_replace("#(?<=[\\s,(])($keywords1)(?=[\\s,)])#i", "\n\$1", $sql);

		// reduce spaces
		$sql = preg_replace('#[ \t]{2,}#', " ", $sql);

		$sql = wordwrap($sql, 100);
		$sql = preg_replace("#([ \t]*\r?\n){2,}#", "\n", $sql);

		// syntax highlight
		$sql = htmlSpecialChars($sql);
		$sql = preg_replace_callback("#(/\\*.+?\\*/)|(\\*\\*.+?\\*\\*)|(?<=[\\s,(])($keywords1)(?=[\\s,)])|(?<=[\\s,(=])($keywords2)(?=[\\s,)=])#is", function($matches) {
			if (!empty($matches[1])) // comment
				return '<em style="color:gray">' . $matches[1] . '</em>';

			if (!empty($matches[2])) // error
				return '<strong style="color:red">' . $matches[2] . '</strong>';

			if (!empty($matches[3])) // most important keywords
				return '<strong style="color:blue">' . $matches[3] . '</strong>';

			if (!empty($matches[4])) // other keywords
				return '<strong style="color:green">' . $matches[4] . '</strong>';
		}, $sql);

		return '<pre class="dump">' . trim($sql) . "</pre>\n";
	}



	/********************* Nette\Object behaviour ****************d*g**/



	/**
	 * @return Nette\Reflection\ClassReflection
	 */
	public /**/static/**/ function getReflection()
	{
		return new Nette\Reflection\ClassReflection(/*5.2*$this*//**/get_called_class()/**/);
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
		throw new \MemberAccessException("Cannot unset the property {$this->reflection->name}::\$$name.");
	}

}
