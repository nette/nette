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
 * Reflection metadata class for a database.
 *
 * @author     Jakub Vrana
 * @property-write Nette\Database\Connection $connection
 */
class ConventionalReflection extends Nette\Object implements Nette\Database\IReflection
{
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



	public function setConnection(Nette\Database\Connection $connection)
	{}

}
