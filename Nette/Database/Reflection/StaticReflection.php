<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Database\Reflection;

use Nette;



/**
 * Statically configured reflection
 *
 * @author     Jan Dolecek
 * @property-write Nette\Database\Connection $connection
 */
class StaticReflection extends Nette\Object implements Nette\Database\IReflection
{
	/** @var array For each table: { primary, hasMany, belongsTo } */
	public $structure;



	/**
	 * @param array database structure
	 */
	public function __construct(array $structure = NULL)
	{
		$this->structure = $structure;
	}



	public function setConnection(Nette\Database\Connection $connection)
	{
	}



	public function getPrimary($table)
	{
		if (isset($this->structure[$table]['primary'])) {
			return $this->structure[$table]['primary'];
		}
	}



	public function getHasManyReference($table, $key)
	{
		if (isset($this->structure[$table]['hasMany'][$key])) {
			return $this->structure[$table]['hasMany'][$key];
		}
	}



	public function getBelongsToReference($table, $key)
	{
		if (isset($this->structure[$table]['belongsTo'][$key])) {
			return $this->structure[$table]['belongsTo'][$key];
		}
	}

}
