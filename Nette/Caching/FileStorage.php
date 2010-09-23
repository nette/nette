<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Caching;

use Nette;



/**
 * Cache file storage.
 *
 * @author     David Grudl
 */
class FileStorage extends Nette\Object implements ICacheStorage
{
	/**
	 * Atomic thread safe logic:
	 *
	 * 1) reading: open(r+b), lock(SH), read
	 *     - delete?: delete*, close
	 * 2) deleting: delete*
	 * 3) writing: open(r+b || wb), lock(EX), truncate*, write data, write meta, close
	 *
	 * delete* = try unlink, if fails (on NTFS) { lock(EX), truncate, close, unlink } else close (on ext3)
	 */

	/**#@+ @internal cache file structure */
	const META_HEADER_LEN = 28; // 22b signature + 6b meta-struct size + serialized meta-struct + data
	// meta structure: array of
	const META_TIME = 'time'; // timestamp
	const META_SERIALIZED = 'serialized'; // is content serialized?
	const META_EXPIRE = 'expire'; // expiration timestamp
	const META_DELTA = 'delta'; // relative (sliding) expiration
	const META_ITEMS = 'di'; // array of dependent items (file => timestamp)
	const META_CALLBACKS = 'callbacks'; // array of callbacks (function, args)
	/**#@-*/

	/**#@+ additional cache structure */
	const FILE = 'file';
	const HANDLE = 'handle';
	/**#@-*/


	/** @var float  probability that the clean() routine is started */
	public static $gcProbability = 0.001;

	/** @var bool */
	public static $useDirectories;

	/** @var string */
	private $dir;

	/** @var bool */
	private $useDirs;

	/** @var Nette\Context */
	private $context;



	public function __construct($dir, Nette\Context $context = NULL)
	{
		if (self::$useDirectories === NULL) {
			// checks whether directory is writable
			$uniq = uniqid('_', TRUE);
			umask(0000);
			if (!@mkdir("$dir/$uniq", 0777)) { // @ - is escalated to exception
				throw new \InvalidStateException("Unable to write to directory '$dir'. Make this directory writable.");
			}

			// tests subdirectory mode
			self::$useDirectories = !ini_get('safe_mode');
			if (!self::$useDirectories && @file_put_contents("$dir/$uniq/_", '') !== FALSE) { // @ - error is expected
				self::$useDirectories = TRUE;
				unlink("$dir/$uniq/_");
			}
			@rmdir("$dir/$uniq"); // @ - directory may not already exist
		}

		$this->dir = $dir;
		$this->useDirs = (bool) self::$useDirectories;
		$this->context = $context;

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

		$this->delete($meta[self::FILE], $meta[self::HANDLE]); // meta[handle] & meta[file] was added by readMeta()
		return FALSE;
	}



	/**
	 * Writes item into the cache.
	 * @param  string key
	 * @param  mixed  data
	 * @param  array  dependencies
	 * @return void
	 */
	public function write($key, $data, array $dp)
	{
		$meta = array(
			self::META_TIME => microtime(),
		);

		if (!empty($dp[Cache::EXPIRE])) {
			if (empty($dp[Cache::SLIDING])) {
				$meta[self::META_EXPIRE] = $dp[Cache::EXPIRE] + time(); // absolute time
			} else {
				$meta[self::META_DELTA] = (int) $dp[Cache::EXPIRE]; // sliding time
			}
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
		if ($this->useDirs && !is_dir($dir = dirname($cacheFile))) {
			umask(0000);
			if (!mkdir($dir, 0777)) {
				return;
			}
		}
		$handle = @fopen($cacheFile, 'r+b'); // @ - file may not exist
		if (!$handle) {
			$handle = fopen($cacheFile, 'wb');
			if (!$handle) {
				return;
			}
		}

		if (!empty($dp[Cache::TAGS]) || isset($dp[Cache::PRIORITY])) {
			if (!$this->context) {
				throw new \InvalidStateException('CacheJournal has not been provided.');
			}
			$this->getJournal()->write($cacheFile, $dp);
		}

		flock($handle, LOCK_EX);
		ftruncate($handle, 0);

		if (!is_string($data)) {
			$data = serialize($data);
			$meta[self::META_SERIALIZED] = TRUE;
		}

		$head = serialize($meta) . '?>';
		$head = '<?php //netteCache[01]' . str_pad((string) strlen($head), 6, '0', STR_PAD_LEFT) . $head;
		$headLen = strlen($head);
		$dataLen = strlen($data);

		do {
			if (fwrite($handle, str_repeat("\x00", $headLen), $headLen) !== $headLen) {
				break;
			}

			if (fwrite($handle, $data, $dataLen) !== $dataLen) {
				break;
			}

			fseek($handle, 0);
			if (fwrite($handle, $head, $headLen) !== $headLen) {
				break;
			}

			fclose($handle);
			return TRUE;
		} while (FALSE);

		$this->delete($cacheFile, $handle);
	}



	/**
	 * Removes item from the cache.
	 * @param  string key
	 * @return void
	 */
	public function remove($key)
	{
		$this->delete($this->getCacheFile($key));
	}



	/**
	 * Removes items from the cache by conditions & garbage collector.
	 * @param  array  conditions
	 * @return void
	 */
	public function clean(array $conds)
	{
		$all = !empty($conds[Cache::ALL]);
		$collector = empty($conds);

		// cleaning using file iterator
		if ($all || $collector) {
			$now = time();
			foreach (Nette\Finder::find('/c*', '/c*/*')->from($this->dir)->limitDepth(1)->childFirst() as $entry) {
				$path = (string) $entry;
				if ($entry->isDir()) { // collector: remove empty dirs
					@rmdir($path); // @ - removing dirs is not necessary
					continue;
				}
				if ($all) {
					$this->delete($path);

				} else { // collector
					$meta = $this->readMeta($path, LOCK_SH);
					if (!$meta) continue;

					if (!empty($meta[self::META_EXPIRE]) && $meta[self::META_EXPIRE] < $now) {
						$this->delete($path, $meta[self::HANDLE]);
						continue;
					}

					fclose($meta[self::HANDLE]);
				}
			}

			if ($this->context) {
				$this->getJournal()->clean($conds);
			}
			return;
		}

		// cleaning using journal
		if ($this->context) {
			foreach ($this->getJournal()->clean($conds) as $file) {
				$this->delete($file);
			}
		}
	}



	/**
	 * Reads cache data from disk.
	 * @param  string  file path
	 * @param  int     lock mode
	 * @return array|NULL
	 */
	protected function readMeta($file, $lock)
	{
		$handle = @fopen($file, 'r+b'); // @ - file may not exist
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
		if ($this->useDirs) {
			$key = explode(Cache::NAMESPACE_SEPARATOR, $key, 2);
			return $this->dir . '/c' . (isset($key[1]) ? '-' . urlencode($key[0]) . '/_' . urlencode($key[1]) : '_' . urlencode($key[0]));
		} else {
			return $this->dir . '/c_' . urlencode($key);
		}
	}



	/**
	 * Deletes and closes file.
	 * @param  string
	 * @param  resource
	 * @return void
	 */
	private static function delete($file, $handle = NULL)
	{
		if (@unlink($file)) { // @ - file may not already exist
			if ($handle) fclose($handle);
			return;
		}

		if (!$handle) {
			$handle = @fopen($file, 'r+'); // @ - file may not exist
		}
		if ($handle) {
			flock($handle, LOCK_EX);
			ftruncate($handle, 0);
			fclose($handle);
			@unlink($file); // @ - file may not already exist
		}
	}



	/**
	 * @return ICacheJournal
	 */
	protected function getJournal()
	{
		return $this->context->getService('Nette\\Caching\\ICacheJournal');
	}

}
