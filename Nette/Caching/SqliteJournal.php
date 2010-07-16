<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Caching
 */

namespace Nette\Caching;

use Nette;



/**
 * Provides SQLite based cache journal backend.
 *
 * @author     Jan Smitka
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Caching
 */
class SqliteJournal extends Nette\Object implements ICacheJournal
{
	/** @var \SQLiteDatabase */
	private $database;



	public function __construct($dir)
	{
		if (!self::isAvailable()) {
			throw new \NotSupportedException("SQLite extension is required for storing tags and priorities.");
		}

		$initialized = file_exists($file = $dir . '/cachejournal.sdb');
		$this->database = new \SQLiteDatabase($file);
		if (!$initialized) {
			$this->database->queryExec(
				'CREATE TABLE cache (entry VARCHAR NOT NULL, priority INTEGER, tag VARCHAR); '
				. 'CREATE INDEX IDX_ENTRY ON cache (entry); '
				. 'CREATE INDEX IDX_PRI ON cache (priority); '
				. 'CREATE INDEX IDX_TAG ON cache (tag);'
			);
		}
	}



	/**
	 * Returns whether the SqliteCacheJournal is able to operate.
	 * @return bool
	 */
	public static function isAvailable()
	{
		return extension_loaded('sqlite');
	}



	/**
	 * Writes entry information into the journal.
	 * @param  string $key
	 * @param  array  $dependencies
	 * @return bool
	 */
	public function write($key, array $dependencies)
	{
		$entry = sqlite_escape_string($key);
		$query = '';
		if (!empty($dependencies[Cache::TAGS])) {
			foreach ((array) $dependencies[Cache::TAGS] as $tag) {
				$query .= "INSERT INTO cache (entry, tag) VALUES ('$entry', '" . sqlite_escape_string($tag) . "'); ";
			}
		}
		if (!empty($dependencies[Cache::PRIORITY])) {
			$query .= "INSERT INTO cache (entry, priority) VALUES ('$entry', '" . ((int) $dependencies[Cache::PRIORITY]) . "'); ";
		}

		if (!$this->database->queryExec("BEGIN; DELETE FROM cache WHERE entry = '$entry'; $query COMMIT;")) {
			$this->database->queryExec('ROLLBACK');
			return FALSE;
		}

		return TRUE;
	}



	/**
	 * Cleans entries from journal.
	 * @param  array  $conditions
	 * @return array of removed items or NULL when performing a full cleanup
	 */
	public function clean(array $conditions)
	{
		if (!empty($conditions[Cache::ALL])) {
			$this->database->queryExec('DELETE FROM CACHE;');
			return;
		}

		$query = array();

		if (!empty($conditions[Cache::TAGS])) {
			$tags = array();
			foreach ((array) $conditions[Cache::TAGS] as $tag) {
				$tags[] = "'" . sqlite_escape_string($tag) . "'";
			}
			$query[] = 'tag IN(' . implode(', ', $tags) . ')';
		}

		if (isset($conditions[Cache::PRIORITY])) {
			$query[] = 'priority <= ' . ((int) $conditions[Cache::PRIORITY]);
		}

		if (!empty($query)) {
			$query = implode(' OR ', $query);
			$entries = $this->database->singleQuery("SELECT entry FROM cache WHERE $query", FALSE);
			$this->database->queryExec("DELETE FROM cache WHERE $query");
			return $entries;

		} else {
			return array();
		}
	}

}
