<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Database\Reflection;

use Nette;



/**
 * Reflection metadata class for a database. TEMPORARY SOLUTION
 *
 * @author     Jakub Vrana
 */
class DatabaseReflection extends Nette\Object
{
	const FIELD_TEXT = 'string',
		FIELD_BINARY = 'bin',
		FIELD_BOOL = 'bool',
		FIELD_INTEGER = 'int',
		FIELD_FLOAT = 'float',
		FIELD_DATETIME = 'datetime';

	/** @var string */
	private $primary;

	/** @var string */
	private $foreign;

	/** @var string */
	private $table;



	/**
	 * Create conventional structure.
	 * @param  string %s stands for table name
	 * @param  string %1$s stands for key used after ->, %2$s for table name
	 * @param  string %1$s stands for key used after ->, %2$s for table name
	 */
	public function __construct($primary = 'id', $foreign = '%s_id', $table = '%s')
	{
		$this->primary = $primary;
		$this->foreign = $foreign;
		$this->table = $table;
	}



	public function getPrimary($table)
	{
		return sprintf($this->primary, $table);
	}



	public function getReferencingColumn($name, $table)
	{
		return $this->getReferencedColumn($table, $name);
	}



	public function getReferencedColumn($name, $table)
	{
		if ($this->table !== '%s' && preg_match('(^' . str_replace('%s', '(.*)', preg_quote($this->table)) . '$)', $name, $match)) {
			$name = $match[1];
		}
		return sprintf($this->foreign, $name, $table);
	}



	public function getReferencedTable($name, $table)
	{
		return sprintf($this->table, $name, $table);
	}



	/**
	 * Heuristic type detection.
	 * @param  string
	 * @return string
	 * @internal
	 */
	public static function detectType($type)
	{
		static $types, $patterns = array(
			'BYTEA|BLOB|BIN' => self::FIELD_BINARY,
			'TEXT|CHAR' => self::FIELD_TEXT,
			'YEAR|BYTE|COUNTER|SERIAL|INT|LONG' => self::FIELD_INTEGER,
			'CURRENCY|REAL|MONEY|FLOAT|DOUBLE|DECIMAL|NUMERIC|NUMBER' => self::FIELD_FLOAT,
			'TIME|DATE' => self::FIELD_DATETIME,
			'BOOL|BIT' => self::FIELD_BOOL,
		);

		if (!isset($types[$type])) {
			$types[$type] = 'string';
			foreach ($patterns as $s => $val) {
				if (preg_match("#$s#i", $type)) {
					return $types[$type] = $val;
				}
			}
		}
		return $types[$type];
	}

}
