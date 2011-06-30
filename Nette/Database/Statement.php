<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
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
 */
class Statement extends \PDOStatement
{
	/** @var Connection */
	private $connection;

	/** @var float */
	public $time;

	/** @var array */
	private $types;



	protected function __construct(Connection $connection)
	{
		$this->connection = $connection;
		$this->setFetchMode(PDO::FETCH_CLASS, 'Nette\Database\Row', array($this));
	}



	/**
	 * @return Connection
	 */
	public function getConnection()
	{
		return $this->connection;
	}



	/**
	 * Executes statement.
	 * @param  array
	 * @return Statement  provides a fluent interface
	 */
	public function execute($params = array())
	{
		static $types = array('boolean' => PDO::PARAM_BOOL, 'integer' => PDO::PARAM_INT,
			'resource' => PDO::PARAM_LOB, 'NULL' => PDO::PARAM_NULL);

		foreach ($params as $key => $value) {
			$type = gettype($value);
			$this->bindValue(is_int($key) ? $key + 1 : $key, $value, isset($types[$type]) ? $types[$type] : PDO::PARAM_STR);
		}

		$time = microtime(TRUE);
		try {
			parent::execute();
		} catch (\PDOException $e) {
			$e->queryString = $this->queryString;
			throw $e;
		}
		$this->time = microtime(TRUE) - $time;
		$this->connection->__call('onQuery', array($this, $params)); // $this->connection->onQuery() in PHP 5.3

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



	/**
	 * Normalizes result row.
	 * @param  array
	 * @return array
	 */
	public function normalizeRow($row)
	{
		if ($this->types === NULL) {
			$this->types = array();
			if ($this->connection->getSupplementalDriver()->supports['meta']) { // workaround for PHP bugs #53782, #54695
				foreach ($row as $key => $foo) {
					$type = $this->getColumnMeta(count($this->types));
					if (isset($type['native_type'])) {
						$this->types[$key] = Reflection\DatabaseReflection::detectType($type['native_type']);
					}
				}
			}
		}

		foreach ($this->types as $key => $type) {
			$value = $row[$key];
			if ($value === NULL || $value === FALSE || $type === Reflection\DatabaseReflection::FIELD_TEXT) {

			} elseif ($type === Reflection\DatabaseReflection::FIELD_INTEGER) {
				$row[$key] = is_float($tmp = $value * 1) ? $value : $tmp;

			} elseif ($type === Reflection\DatabaseReflection::FIELD_FLOAT) {
				$row[$key] = (string) ($tmp = (float) $value) === $value ? $tmp : $value;

			} elseif ($type === Reflection\DatabaseReflection::FIELD_BOOL) {
				$row[$key] = ((bool) $value) && $value !== 'f' && $value !== 'F';
			}
		}

		return $this->connection->getSupplementalDriver()->normalizeRow($row, $this);
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
