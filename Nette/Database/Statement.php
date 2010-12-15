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
 * Represents a prepared statement / result set.
 *
 * @author     David Grudl
 */
class Statement extends \PDOStatement
{
	/** @var Connection */
	private $connection;


	protected function __construct(Connection $connection)
	{
		$this->connection = $connection;
		$this->setFetchMode(PDO::FETCH_CLASS, 'Nette\Database\Row', array($this));
	}



	/**
	 * Executes statement.
	 * @param  array
	 * @return Statement  provides a fluent interface
	 */
	public function execute($params = array())
	{
		static $types = array('boolean' => PDO::PARAM_BOOL, 'integer' => PDO::PARAM_INT, 'resource' => PDO::PARAM_LOB, 'NULL' => PDO::PARAM_NULL);
		foreach ($params as $key => $value) {
			$type = gettype($value);
			$this->bindValue(is_int($key) ? $key + 1 : ":$key", $value, isset($types[$type]) ? $types[$type] : PDO::PARAM_STR);
		}

		$time = microtime(TRUE);

		parent::execute();

		if ($this->connection->profiler) {
			$this->connection->profiler->logQuery($this->queryString, $params, microtime(TRUE) - $time, $this->rowCount());
		}

		return $this;
	}



	/**
	 * Fetches into an array where the 1st column is a key and all subsequent columns are values.
	 * @return array
	 */
	public function fetchPairs()
	{
		return $this->fetchAll(PDO::FETCH_KEY_PAIR); // since PHP 5.2.3
	}



	/********************* misc tools ****************d*g**/



	/**
	 * Displays complete result set as HTML table for debug purposes.
	 * @return void
	 */
	public function dump()
	{
		echo "\n<table class=\"dump\">\n<caption>" . htmlSpecialChars($this->queryString) . "</caption>\n";
		if (!$this->columnCount()) {
			echo "\t<tr>\n\t\t<th>Affected rows:</th>\n\t\t<td>", $this->rowCount(), "</td>\n\t</tr>\n</table>\n";
			return;
		}
		$i = 0;
		foreach ($this as $row) {
			if ($i === 0) {
				echo "<thead>\n\t<tr>\n\t\t<th>#row</th>\n";
				foreach ($row as $col => $foo) {
					echo "\t\t<th>" . htmlSpecialChars($col) . "</th>\n";
				}
				echo "\t</tr>\n</thead>\n<tbody>\n";
			}
			echo "\t<tr>\n\t\t<th>", $i, "</th>\n";
			foreach ($row as $col) {
				//if (is_object($col)) $col = $col->__toString();
				echo "\t\t<td>", htmlSpecialChars($col), "</td>\n";
			}
			echo "\t</tr>\n";
			$i++;
		}

		if ($i === 0) {
			echo "\t<tr>\n\t\t<td><em>empty result set</em></td>\n\t</tr>\n</table>\n";
		} else {
			echo "</tbody>\n</table>\n";
		}
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
