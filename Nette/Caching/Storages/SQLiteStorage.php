<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Caching\Storages;

use Nette,
	Nette\Caching\Cache;


/**
 * SQLite storage.
 *
 * @author     David Grudl
 */
class SQLiteStorage extends Nette\Object implements Nette\Caching\IStorage
{
	/** @var PDO */
	private $pdo;


	public function __construct($path = ':memory:')
	{
		$this->pdo = new \PDO('sqlite:' . $path, NULL, NULL, array(\PDO::ATTR_PERSISTENT => TRUE));
		$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$this->pdo->exec('
			PRAGMA foreign_keys = ON;
			CREATE TABLE IF NOT EXISTS cache (
				key BLOB NOT NULL PRIMARY KEY, data BLOB NOT NULL
			);
			CREATE TABLE IF NOT EXISTS tags (
				key BLOB NOT NULL REFERENCES cache ON DELETE CASCADE,
				tag BLOB NOT NULL
			);
			CREATE INDEX IF NOT EXISTS tags_key ON tags(key);
			CREATE INDEX IF NOT EXISTS tags_tag ON tags(tag);
		');
	}


	/**
	 * Read from cache.
	 * @param  string key
	 * @return mixed|NULL
	 */
	public function read($key)
	{
		$stmt = $this->pdo->prepare('SELECT data FROM cache WHERE key=?');
		$stmt->execute(array($key));
		if ($res = $stmt->fetchColumn()) {
			return unserialize($res);
		}
	}


	/**
	 * Prevents item reading and writing. Lock is released by write() or remove().
	 * @param  string key
	 * @return void
	 */
	public function lock($key)
	{
	}


	/**
	 * Writes item into the cache.
	 * @param  string key
	 * @param  mixed  data
	 * @param  array  dependencies
	 * @return void
	 */
	public function write($key, $data, array $dependencies)
	{
		$this->pdo->prepare('BEGIN TRANSACTION');
		$this->pdo->prepare('REPLACE INTO cache (key, data) VALUES (?, ?)')
			->execute(array($key, serialize($data)));

		if (!empty($dependencies[Cache::TAGS])) {
			foreach ((array) $dependencies[Cache::TAGS] as $tag) {
				$arr[] = $key;
				$arr[] = $tag;
			}
			$this->pdo->prepare('INSERT INTO tags (key, tag) SELECT ?, ?' . str_repeat('UNION SELECT ?, ?', count($arr) / 2 - 1))
				->execute($arr);
		}
		$this->pdo->prepare('COMMIT');
	}


	/**
	 * Removes item from the cache.
	 * @param  string key
	 * @return void
	 */
	public function remove($key)
	{
		$this->pdo->prepare('DELETE FROM cache WHERE key=?')
			->execute(array($key));
	}


	/**
	 * Removes items from the cache by conditions & garbage collector.
	 * @param  array  conditions
	 * @return void
	 */
	public function clean(array $conditions)
	{
		if (!empty($conditions[Cache::ALL])) {
			$this->pdo->prepare('DELETE FROM cache');

		} elseif (!empty($conditions[Cache::TAGS])) {
			$tags = (array) $conditions[Cache::TAGS];
			$this->pdo->prepare('DELETE FROM cache WHERE key IN (SELECT key FROM tags WHERE tag IN (?'
				. str_repeat(',?', count($tags) - 1) . '))')->execute($tags);
		}
	}

}
