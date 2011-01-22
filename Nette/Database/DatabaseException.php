<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Database;

use \PDOException,
	\Nette\IDebugPanel,
	\Nette\String;


/**
 * Simulates PDO unbuffered query binding for debuging.
 *
 * @author     Mikuláš Dítě
 */
class DatabaseException extends PDOException implements IDebugPanel
{

	/** @var string PDOStatement queryString */
	private $rawQuery;
	
	/** @var array PDOStatement bindings */
	private $params;
	
	/** @var string queryString with bindings */
	private $sql;



	public function __construct($message, $code = 0, $previous = NULL, $rawQuery = '', array $params = NULL)
	{
		parent::__construct($message, $code, $previous);
		
		$this->rawQuery = $rawQuery;
		$this->params = $params;
	}



	public function getSql()
	{
		if ($this->sql === NULL) {
			$this->sql = $this->rawQuery;
			foreach ($this->params as $key => $value) {
				$this->sql = String::replace($this->rawQuery, is_int($key) ? '~\?~' : '~:' . preg_quote($key, '~') . '~', $this->quote($value), 1);
			}
		}

		return $this->sql;
	}



	private function quote($value)
	{
		switch (gettype($value)) {
			case 'boolean':
			case 'integer':
			case 'double':
				$quoted = $value;
				break;
			case 'array':
				$quoted = array_walk(implode(', ', $value), callback($this, 'quote'));
				break;
			case 'NULL':
				$quoted = 'NULL';
				break;
			case 'string':
			default:
				$quoted = "'$value'";
				break;
		}

		return $quoted;
	}



	public function getTab()
	{
		return 'SQL';
	}



	public function getPanel()
	{
		$highlight = String::replace($this->getSql(), '~\s*(=|\+|-|/)\s*~i', ' $1 ');
		$highlight = String::replace($highlight, '~(LEFT |RIGHT |INNER |OUTER )?JOIN|WHERE|GROUP BY|UNION~i', "<br>$0");
		$highlight = String::replace($highlight, '~(DELETE|FROM|HAVING|INSERT|(LEFT |RIGHT |INNER |OUTER )?JOIN|MERGE|ORDER BY|SELECT|UNION|UPDATE|WHERE|=|\(|\)|\+|-|\|)~i', '<span style="color: #D24; font-weight: bold">$1</span>');
		$highlight = String::replace($highlight, '~(?<=`)[^`]*?(?=`)~i', '<span style="font-style: italic;">$0</span>');
		$highlight = String::replace($highlight, '~\s{2,}~', ' ');
		return '<pre>' . $highlight . '</pre>';
	}



	public function getId()
	{
	}

}
