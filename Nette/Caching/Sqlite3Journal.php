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
 * Provides SQLite3 based cache journal backend.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Caching
 */
class Sqlite3Journal extends Nette\Object implements ICacheJournal
{
	/** @var string */
	private $dir;

	/** @var \SQLite3 */
	private $database;




	public function __construct($dir)
	{
		$this->dir = $dir;
	}



	/**
	 * Returns whether the SqliteCacheJournal is able to operate.
	 * @return bool
	 */
	public static function isAvailable()
	{
		return extension_loaded('sqlite3');
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
		
		if (!$this->getDatabase()->exec("BEGIN; DELETE FROM cache WHERE entry = '$entry'; $query COMMIT;")) {
			$this->getDatabase()->exec('ROLLBACK');
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
			if (self::isAvailable())
				$this->getDatabase()->exec('DELETE FROM CACHE;');
			
			return;
		} else {
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
				$result = $this->getDatabase()->query("SELECT entry FROM cache WHERE $query");
				$entries = array();
				while ($entry = $result->fetchArray(SQLITE3_NUM)) {
					$entries[] = $entry[0];
				}				
				$this->getDatabase()->exec("DELETE FROM cache WHERE $query");
				return $entries;
			} else {
				return array();
			}
		}
	}



	/**
	 * Gets the database object.
	 * @return \SQLite3
	 */
	protected function getDatabase()
	{
		if ($this->database === NULL) {
			if (!self::isAvailable()) {
				throw new \InvalidStateException("SQLite3 extension is required for storing tags and priorities.");
			}
			
			// init the journal
			$initialized = file_exists($file = ($this->dir . '/cachejournal.db'));
			$this->database = new \SQLite3($file);
			if (!$initialized) {
				$this->database->exec(
					'CREATE TABLE cache (entry VARCHAR NOT NULL, priority INTEGER, tag VARCHAR); '
					. 'CREATE INDEX IDX_ENTRY ON cache (entry); '
					. 'CREATE INDEX IDX_PRI ON cache (priority); '
					. 'CREATE INDEX IDX_TAG ON cache (tag);'
				);
			}
		}
		
		return $this->database;
	}

}