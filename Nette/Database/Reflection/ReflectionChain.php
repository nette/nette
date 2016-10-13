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
 * Chain multiple reflections, trying them one by one until it gets a result
 *
 * @author     Jan Dolecek
 * @property-write Nette\Database\Connection $connection
 */
class ReflectionChain extends Nette\Object implements Nette\Database\IReflection
{
	/** @var Nette\Database\IReflection */
	protected $reflections = array();



	/**
	 * Add new database reflection to chain
	 * @param bool To be used as first one? Otherwise last one.
	 */
	public function addReflection(\Nette\Database\IReflection $reflection, $first = FALSE)
	{
		if ($first) {
			array_unshift($this->reflections, $reflection);
		} else {
			$this->reflections[] = $reflection;
		}
	}



	public function setConnection(Nette\Database\Connection $connection)
	{}



	public function getPrimary($table)
	{
		return $this->chainMethod(__FUNCTION__, func_get_args());
	}



	public function getHasManyReference($table, $key)
	{
		return $this->chainMethod(__FUNCTION__, func_get_args());
	}



	public function getBelongsToReference($table, $key)
	{
		return $this->chainMethod(__FUNCTION__, func_get_args());
	}



	private function chainMethod($method, $args)
	{
		foreach ($this->reflections as $reflection) {
			try {
				$ret = call_user_func_array(array($reflection, $method), $args);
				if ($ret !== NULL) {
					return $ret;
				}
			} catch(\PDOException $e) {
			}
		}
	}

}
