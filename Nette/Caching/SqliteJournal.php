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
 * Provides SQLite/SQLite3 based cache journal backend.
 *
 * @author     Jan Smitka
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Caching
 */
class SqliteJournal extends Nette\Object implements ICacheJournal
{
	/** @var \SQLite3|SQLiteMimic */
	private $database;



	/**
	 * Returns whether the SqliteJournal is able to operate.
	 * @return bool
	 */
	public static function isAvailable()
	{
		return extension_loaded('sqlite') || extension_loaded('sqlite3');
	}



	public function __construct($file)
	{
		if (!self::isAvailable()) {
			throw new \NotSupportedException("SQLite or SQLite3 extension is required for storing tags and priorities.");
		}

		$this->database = extension_loaded('sqlite') ? new SQLiteMimic($file) : new \SQLite3($file);
		@$this->database->exec( // simulates IGNORE IF EXISTS (available since SQLite3 )
			'CREATE TABLE cache (entry VARCHAR NOT NULL, priority INTEGER, tag VARCHAR); '
			. 'CREATE INDEX IDX_ENTRY ON cache (entry); '
			. 'CREATE INDEX IDX_PRI ON cache (priority); '
			. 'CREATE INDEX IDX_TAG ON cache (tag);'
		);
	}



	/**
	 * Writes entry information into the journal.
	 * @param  string $key
	 * @param  array  $dependencies
	 * @return bool
	 */
	public function write($key, array $dependencies)
	{
		$entry = $this->database->escapeString($key);
		$query = '';
		if (!empty($dependencies[Cache::TAGS])) {
			foreach ((array) $dependencies[Cache::TAGS] as $tag) {
				$query .= "INSERT INTO cache (entry, tag) VALUES ('$entry', '" . $this->database->escapeString($tag) . "'); ";
			}
		}
		if (!empty($dependencies[Cache::PRIORITY])) {
			$query .= "INSERT INTO cache (entry, priority) VALUES ('$entry', '" . ((int) $dependencies[Cache::PRIORITY]) . "'); ";
		}

		if (!$this->database->exec("BEGIN; DELETE FROM cache WHERE entry = '$entry'; $query COMMIT;")) {
			$this->database->exec('ROLLBACK');
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
			$this->database->exec('DELETE FROM CACHE;');
			return;
		}

		$query = array();
		if (!empty($conditions[Cache::TAGS])) {
			$tags = array();
			foreach ((array) $conditions[Cache::TAGS] as $tag) {
				$tags[] = "'" . $this->database->escapeString($tag) . "'";
			}
			$query[] = 'tag IN(' . implode(', ', $tags) . ')';
		}

		if (isset($conditions[Cache::PRIORITY])) {
			$query[] = 'priority <= ' . ((int) $conditions[Cache::PRIORITY]);
		}

		$entries = array();
		if (!empty($query)) {
			$query = implode(' OR ', $query);
			$result = $this->database->query("SELECT entry FROM cache WHERE $query");
			if ($result instanceof \SQLiteResult) {
				while ($entry = $result->fetchSingle()) $entries[] = $entry;
			} else {
				while ($entry = $result->fetchArray(SQLITE3_NUM)) $entries[] = $entry[0];
			}
			$this->database->exec("DELETE FROM cache WHERE $query");
		}
		return $entries;
	}

}



if (class_exists('SQLiteDatabase')) {
	/**
	 * SQLite3 API mimic for SQLiteDatabase
	 *
	 * @copyright  Copyright (c) 2004, 2010 David Grudl
	 * @package    Nette\Caching
	 * @internal
	 */
	class SQLiteMimic extends \SQLiteDatabase
	{

		function exec($sql)
		{
			return $this->queryExec($sql);
		}

		function escapeString($s)
		{
			return sqlite_escape_string($s);
		}

	}
}
