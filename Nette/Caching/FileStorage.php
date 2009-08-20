<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Caching
 */

/*namespace Nette\Caching;*/



require_once dirname(__FILE__) . '/../Object.php';

require_once dirname(__FILE__) . '/../Caching/ICacheStorage.php';



/**
 * Cache file storage.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Caching
 */
class FileStorage extends /*Nette\*/Object implements ICacheStorage
{
	/**
	 * Atomic thread safe logic:
	 *
	 * 1) reading: open(r+b), lock(SH), read
	 *     - delete?: delete*, close
	 * 2) deleting: open(r+b), delete*, close
	 * 3) writing: open(r+b || wb), lock(EX), truncate*, write data, write meta, close
	 *
	 * delete* = lock(EX), try unlink, if fails (on NTFS) { truncate, close, unlink } else close (on ext3)
	 */

	/**#@+ internal cache file structure */
	const META_HEADER_LEN = 28; // 22b signature + 6b meta-struct size + serialized meta-struct + data
	// meta structure: array of
	const META_TIME = 'time'; // timestamp
	const META_SERIALIZED = 'serialized'; // is content serialized?
	const META_PRIORITY = 'priority'; // priority
	const META_EXPIRE = 'expire'; // expiration timestamp
	const META_DELTA = 'delta'; // relative (sliding) expiration
	const META_ITEMS = 'di'; // array of dependent items (file => timestamp)
	const META_TAGS = 'tags'; // array of tags (tag => [foo])
	const META_CALLBACKS = 'callbacks'; // array of callbacks (function, args)
	/**#@-*/

	/**#@+ additional cache structure */
	const FILE = 'file';
	const HANDLE = 'handle';
	/**#@-*/


	/** @var float  probability that the clean() routine is started */
	public static $gcProbability = 0.001;

	/** @var string */
	private $dir;

	/** @var bool */
	private $useSubdir;



	public function __construct($dir)
	{
		$this->useSubdir = !ini_get('safe_mode');
		$this->dir = $dir;
		if (!$this->useSubdir && (!is_dir($dir) || !is_writable($dir))) {
			throw new /*\*/InvalidStateException("Temporary directory '$dir' is not writable.");
		}

		if (mt_rand() / mt_getrandmax() < self::$gcProbability) {
			$this->clean(array());
		}
	}



	/**
	 * Read from cache.
	 * @param  string key
	 * @return mixed|NULL
	 */
	public function read($key)
	{
		$meta = $this->readMeta($this->getCacheFile($key), LOCK_SH);
		if ($meta && $this->verify($meta)) {
			return $this->readData($meta); // calls fclose()

		} else {
			return NULL;
		}
	}



	/**
	 * Verifies dependencies.
	 * @param  array
	 * @return bool
	 */
	private function verify($meta)
	{
		do {
			if (!empty($meta[self::META_DELTA])) {
				// meta[file] was added by readMeta()
				if (filemtime($meta[self::FILE]) + $meta[self::META_DELTA] < time()) break;
				touch($meta[self::FILE]);

			} elseif (!empty($meta[self::META_EXPIRE]) && $meta[self::META_EXPIRE] < time()) {
				break;
			}

			if (!empty($meta[self::META_CALLBACKS]) && !Cache::checkCallbacks($meta[self::META_CALLBACKS])) {
				break;
			}

			if (!empty($meta[self::META_ITEMS])) {
				foreach ($meta[self::META_ITEMS] as $depFile => $time) {
					$m = $this->readMeta($depFile, LOCK_SH);
					if ($m[self::META_TIME] !== $time) break 2;
					if ($m && !$this->verify($m)) break 2;
				}
			}

			return TRUE;
		} while (FALSE);

		$this->delete($meta[self::HANDLE], $meta[self::FILE]); // meta[handle] & meta[file] was added by readMeta()
		return FALSE;
	}



	/**
	 * Writes item into the cache.
	 * @param  string key
	 * @param  mixed  data
	 * @param  array  dependencies
	 * @return bool  TRUE if no problem
	 */
	public function write($key, $data, array $dp)
	{
		$meta = array(
			self::META_TIME => microtime(),
		);

		if (!is_string($data)) {
			$data = serialize($data);
			$meta[self::META_SERIALIZED] = TRUE;
		}

		if (isset($dp[Cache::PRIORITY])) {
			$meta[self::META_PRIORITY] = (int) $dp[Cache::PRIORITY];
		}

		if (!empty($dp[Cache::EXPIRE])) {
			if (empty($dp[Cache::SLIDING])) {
				$meta[self::META_EXPIRE] = $dp[Cache::EXPIRE] + time(); // absolute time
			} else {
				$meta[self::META_DELTA] = (int) $dp[Cache::EXPIRE]; // sliding time
			}
		}

		if (!empty($dp[Cache::TAGS])) {
			$meta[self::META_TAGS] = array_flip(array_values((array) $dp[Cache::TAGS]));
		}

		if (!empty($dp[Cache::ITEMS])) {
			foreach ((array) $dp[Cache::ITEMS] as $item) {
				$depFile = $this->getCacheFile($item);
				$m = $this->readMeta($depFile, LOCK_SH);
				$meta[self::META_ITEMS][$depFile] = $m[self::META_TIME];
				unset($m);
			}
		}

		if (!empty($dp[Cache::CALLBACKS])) {
			$meta[self::META_CALLBACKS] = $dp[Cache::CALLBACKS];
		}

		$cacheFile = $this->getCacheFile($key);
		$dir = dirname($cacheFile);
		if ($this->useSubdir && !is_dir($dir)) {
			umask(0000);
			if (!@mkdir($dir, 0777, TRUE)) {
				throw new /*\*/InvalidStateException("Unable to create directory '$dir'.");
			}
		}
		$handle = @fopen($cacheFile, 'r+b'); // intentionally @
		if (!$handle) {
			$handle = @fopen($cacheFile, 'wb'); // intentionally @

			if (!$handle) {
				return FALSE;
			}
		}

		flock($handle, LOCK_EX);
		ftruncate($handle, 0);

		$head = serialize($meta) . '?>';
		$head = '<?php //netteCache[01]' . str_pad((string) strlen($head), 6, '0', STR_PAD_LEFT) . $head;
		$headLen = strlen($head);
		$dataLen = strlen($data);

		if (fwrite($handle, str_repeat("\x00", $headLen), $headLen) === $headLen) {
			if (fwrite($handle, $data, $dataLen) === $dataLen) {
				fseek($handle, 0);
				if (fwrite($handle, $head, $headLen) === $headLen) {
					fclose($handle);
					return TRUE;
				}
			}
		}

		$this->delete($handle, $cacheFile);
		return TRUE;
	}



	/**
	 * Removes item from the cache.
	 * @param  string key
	 * @return bool  TRUE if no problem
	 */
	public function remove($key)
	{
		$cacheFile = $this->getCacheFile($key);
		$meta = $this->readMeta($cacheFile, LOCK_EX);
		if ($meta) {
			$this->delete($meta[self::HANDLE], $cacheFile);
		}
		return TRUE;
	}



	/**
	 * Removes items from the cache by conditions & garbage collector.
	 * @param  array  conditions
	 * @return bool  TRUE if no problem
	 */
	public function clean(array $conds)
	{
		$tags = isset($conds[Cache::TAGS]) ? array_flip((array) $conds[Cache::TAGS]) : array();

		$priority = isset($conds[Cache::PRIORITY]) ? $conds[Cache::PRIORITY] : -1;

		$all = !empty($conds[Cache::ALL]);

		$now = time();

		$base = $this->dir . DIRECTORY_SEPARATOR . 'c';
		$iterator = new /*\*/RecursiveIteratorIterator(new /*\*/RecursiveDirectoryIterator($this->dir), /*\*/RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($iterator as $entry) {
			if (strncmp($entry, $base, strlen($base))) {
				continue;
			}
			if ($entry->isDir()) {
				@rmdir((string) $entry); // intentionally @
				continue;
			}
			do {
				$meta = $this->readMeta((string) $entry, LOCK_SH);
				if (!$meta || $all) continue 2;

				if (!empty($meta[self::META_EXPIRE]) && $meta[self::META_EXPIRE] < $now) {
					break;
				}

				if (!empty($meta[self::META_PRIORITY]) && $meta[self::META_PRIORITY] <= $priority) {
					break;
				}

				if (!empty($meta[self::META_TAGS]) && array_intersect_key($tags, $meta[self::META_TAGS])) {
					break;
				}

				fclose($meta[self::HANDLE]);
				continue 2;
			} while (FALSE);

			$this->delete($meta[self::HANDLE], (string) $entry);
		}

		return TRUE;
	}



	/**
	 * Reads cache data from disk.
	 * @param  string  file path
	 * @param  int     lock mode
	 * @return array|NULL
	 */
	protected function readMeta($file, $lock)
	{
		$handle = @fopen($file, 'r+b'); // intentionally @
		if (!$handle) return NULL;

		flock($handle, $lock);

		$head = stream_get_contents($handle, self::META_HEADER_LEN);
		if ($head && strlen($head) === self::META_HEADER_LEN) {
			$size = (int) substr($head, -6);
			$meta = stream_get_contents($handle, $size, self::META_HEADER_LEN);
			$meta = @unserialize($meta); // intentionally @
			if (is_array($meta)) {
				fseek($handle, $size + self::META_HEADER_LEN); // needed by PHP < 5.2.6
				$meta[self::FILE] = $file;
				$meta[self::HANDLE] = $handle;
				return $meta;
			}
		}

		fclose($handle);
		return NULL;
	}



	/**
	 * Reads cache data from disk and closes cache file handle.
	 * @param  array
	 * @return mixed
	 */
	protected function readData($meta)
	{
		$data = stream_get_contents($meta[self::HANDLE]);
		fclose($meta[self::HANDLE]);

		if (empty($meta[self::META_SERIALIZED])) {
			return $data;
		} else {
			return @unserialize($data); // intentionally @
		}
	}



	/**
	 * Returns file name.
	 * @param  string
	 * @return string
	 */
	protected function getCacheFile($key)
	{
		if ($this->useSubdir) {
			$key = explode(Cache::NAMESPACE_SEPARATOR, $key, 2);
			return $this->dir . '/c' . (isset($key[1]) ? '-' . urlencode($key[0]) . '/_' . urlencode($key[1]) : '_' . urlencode($key[0]));
		} else {
			return $this->dir . '/c_' . urlencode($key);
		}
	}



	/**
	 * Deletes and closes file.
	 * @param  resource
	 * @param  string
	 * @return void
	 */
	private static function delete($handle, $file)
	{
		if (@unlink($file)) { // intentionally @
			fclose($handle);
		} else {
			flock($handle, LOCK_EX);
			ftruncate($handle, 0);
			fclose($handle);
			@unlink($file); // intentionally @; not atomic
		}
	}

}
