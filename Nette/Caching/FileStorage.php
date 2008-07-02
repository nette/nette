<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com/
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com/
 * @category   Nette
 * @package    Nette::Caching
 */

/*namespace Nette::Caching;*/



require_once dirname(__FILE__) . '/../Object.php';

require_once dirname(__FILE__) . '/../Caching/ICacheStorage.php';



/**
 * Cache file storage.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Caching
 * @version    $Revision$ $Date$
 */
class FileStorage extends /*Nette::*/Object implements ICacheStorage
{
	/**
	 * Atomic thread safe logic:
	 *
	 * 1) reading: open(r+b), lock(SH), read
	 *     - delete?: lock(EX), truncate*, unlink*, close
	 * 2) deleting: open(r+b), lock(EX), truncate*, unlink*, close
	 * 3) writing: open(r+b || wb), lock(EX), truncate*, write data, write meta, close
	 *
	 * *unlink fails in windows
	 */

	/**
	 * Cache file:
	 *
	 * - 22b signature + 6b meta-struct size + serialized meta-struct + data
	 * - meta structure: array of
	 *     time => timestamp
	 *     serialized => is content serialized?
	 *     priority => priority
	 *     expire => expiration timestamp
	 *     delta => relative (sliding) expiration
	 *     df => array of dependent files (file => timestamp)
	 *     di => array of dependent items (file => timestamp)
	 *     tags => array of tags (tag => [foo])
	 *     consts => array of constants (const => [value])
	 */

	/** @var int */
	const HEADER_LEN = 28;

	/** @var float  probability that the clean() routine is started */
	public static $gcProbability = 0.001;

	/** @var string */
	protected $base;



	public function __construct($base)
	{
		$this->base = $base;
		$dir = dirname($base . '-');

		if (!is_dir($dir) || !is_writable($dir)) {
			throw new /*::*/InvalidStateException("Temporary directory '$dir' is not writable.");
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
		$cacheFile = $this->getCacheFile($key);
		$meta = $this->readMeta($cacheFile, LOCK_SH);
		if (!$meta) return NULL;

		// meta[handle] & meta[file] is added by readMeta()

		// verify dependencies
		do {
			/*
			if (!empty($meta['delta']) || !empty($meta['df'])) {
				clearstatcache();
			}
			*/

			if (!empty($meta['delta'])) {
				if (filemtime($cacheFile) + $meta['delta'] < time()) break;
				touch($cacheFile);

			} elseif (!empty($meta['expire']) && $meta['expire'] < time()) {
				break;
			}

			if (!empty($meta['consts'])) {
				foreach ($meta['consts'] as $const => $value) {
					if (!defined($const) || constant($const) !== $value) break 2;
				}
			}

			if (!empty($meta['df'])) {
				foreach ($meta['df'] as $depFile => $time) {
					if (@filemtime($depFile) <> $time) break 2;  // intentionally @
				}
			}

			if (!empty($meta['di'])) {
				foreach ($meta['di'] as $depFile => $time) {
					$m = $this->readMeta($depFile, LOCK_SH);
					// TODO: check item completely
					if ($m['time'] !== $time) break 2;
					unset($m);
				}
			}

			return $this->readData($meta); // calls fclose()
		} while (FALSE);

		flock($meta['handle'], LOCK_EX);
		ftruncate($meta['handle'], 0);
		@unlink($cacheFile); // intentionally @
		fclose($meta['handle']);
		return NULL;
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
			'time' => microtime(),
		);

		if (!is_string($data)) {
			$data = serialize($data);
			$meta['serialized'] = TRUE;
		}

		if (isset($dp['priority'])) {
			$meta['priority'] = (int) $dp['priority'];
		}

		if (!empty($dp['expire'])) {
			$expire = (int) $dp['expire'];
			if ($expire <= self::EXPIRATION_DELTA_LIMIT) {
				$expire += time();
			}
			if (empty($dp['refresh'])) {
				$meta['expire'] = $expire; // absolute time
			} else {
				$meta['delta'] = $expire - time(); // sliding time
			}
		}

		if (!empty($dp['tags']) && is_array($dp['tags'])) {
			$meta['tags'] = array_flip(array_values($dp['tags']));
		}

		if (!empty($dp['items'])) {
			foreach ($dp['items'] as $item) {
				$depFile = $this->getCacheFile($item);
				$m = $this->readMeta($depFile, LOCK_SH);
				$meta['di'][$depFile] = $m['time'];
				unset($m);
			}
		}

		if (!empty($dp['files'])) {
			//clearstatcache();
			foreach ((array) $dp['files'] as $depFile) {
				$meta['df'][$depFile] = @filemtime($depFile); // intentionally @
			}
		}

		if (!empty($dp['consts'])) {
			foreach ((array) $dp['consts'] as $const) {
				$meta['consts'][$const] = constant($const);
			}
		}

		$cacheFile = $this->getCacheFile($key);
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

		ftruncate($handle, 0);
		@unlink($cacheFile); // intentionally @
		fclose($handle);
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
		if (!$meta) return TRUE;

		ftruncate($meta['handle'], 0);
		@unlink($file); // intentionally @
		fclose($meta['handle']);
		return TRUE;
	}



	/**
	 * Removes items from the cache by conditions & garbage collector.
	 * @param  array  conditions
	 * @return bool  TRUE if no problem
	 */
	public function clean(array $conds)
	{
		$tags = isset($conds['tags']) ? array_flip($conds['tags']) : array();

		$priority = isset($conds['priority']) ? $conds['priority'] : -1;

		$all = !empty($conds['all']);

		$now = time();

		foreach (glob($this->base . '*') as $cacheFile)
		{
			if (!is_file($cacheFile)) continue;

			do {
				$meta = $this->readMeta($cacheFile, LOCK_SH);
				if (!$meta || $all) break;

				if (!empty($meta['expire']) && $meta['expire'] < $now) {
					break;
				}

				if (!empty($meta['priority']) && $meta['priority'] <= $priority) {
					break;
				}

				if (!empty($meta['tags']) && array_intersect_key($tags, $meta['tags'])) {
					break;
				}

				fclose($meta['handle']);
				continue 2;
			} while (FALSE);

			flock($meta['handle'], LOCK_EX);
			ftruncate($meta['handle'], 0);
			@unlink($cacheFile); // intentionally @
			fclose($meta['handle']);
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

		$head = stream_get_contents($handle, self::HEADER_LEN);
		if ($head && strlen($head) === self::HEADER_LEN) {
			$size = (int) substr($head, -6);
			$meta = stream_get_contents($handle, $size, self::HEADER_LEN);
			$meta = @unserialize($meta); // intentionally @
			if (is_array($meta)) {
				fseek($handle, $size + self::HEADER_LEN); // needed by PHP < 5.2.6
				$meta['file'] = $file;
				$meta['handle'] = $handle;
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
		$data = stream_get_contents($meta['handle']);
		fclose($meta['handle']);

		if (empty($meta['serialized'])) {
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
		return $this->base . urlencode($key);
	}

}
