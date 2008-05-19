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
	 *     - delete?: lock(EX), truncate*, unlinkData, unlink*, close
	 * 2) deleting: open(r+b), lock(EX), truncate*, unlinkData, unlink*, close
	 * 3) writing: open(r+b || wb), lock(EX), truncate*, writeData, write, close
	 *
	 * *unlink fails in windows
	 */

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
		// read meta
		$metaFile = $this->getMetaFile($key);
		$meta = $this->readMeta($metaFile, LOCK_SH);
		if (!$meta) return NULL;

		// meta structure:
		// array(
		//     file => cache content file path (the one and only mandatory item)
		//     serialized => is content serialized?
		//     priority => priority
		//     expire => expiration timestamp
		//     delta => relative (sliding) expiration
		//     df => array of dependent files (file => timestamp)
		//     tags => array of tags (tag => [foo])
		//     handle > meta file pointer resource; added by readMeta()
		// )

		// verify dependencies
		do {
			if (!empty($meta['delta']) || !empty($meta['df'])) {
				clearstatcache();
			}

			if (!empty($meta['delta'])) {
				if (filemtime($metaFile) + $meta['delta'] < time()) break;
				touch($metaFile);

			} elseif (!empty($meta['expire']) && $meta['expire'] < time()) {
				break;
			}

			if (!empty($meta['df'])) {
				foreach ($meta['df'] as $depFile => $time) {
					if (@filemtime($depFile) <> $time) break 2;  // intentionally @
				}
			}

			return $this->readData($meta); // calls fclose()
		} while (FALSE);

		flock($meta['handle'], LOCK_EX);
		ftruncate($meta['handle'], 0);
		@unlink($meta['file']); // intentionally @
		@unlink($metaFile); // intentionally @
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
			'file' => $this->getDataFile($key),
		);

		if (!is_string($data)) {
			$data = serialize($data);
			$meta['serialized'] = TRUE;
		}

		if (isset($dp['priority'])) {
			$meta['priority'] = (int) $dp['priority'];
		}

		if (!empty($dp['expire'])) {
			if (empty($dp['refresh'])) {
				$meta['expire'] = (int) $dp['expire']; // absolute time
			} else {
				$meta['delta'] = $dp['expire'] - time(); // sliding time
			}
		}

		if (!empty($dp['tags']) && is_array($dp['tags'])) {
			$meta['tags'] = array_flip(array_values($dp['tags']));
		}

		if (!empty($dp['files']) || !empty($dp['items'])) {
			clearstatcache();
		}

		if (!empty($dp['items'])) {
			foreach ((array) $dp['items'] as $item) {
				$depFile = $this->getDataFile($item);
				$meta['df'][$depFile] = @filemtime($depFile); // intentionally @
			}
		}

		if (!empty($dp['files'])) {
			foreach ((array) $dp['files'] as $depFile) {
				$meta['df'][$depFile] = @filemtime($depFile); // intentionally @
			}
		}

		$metaFile = $this->getMetaFile($key);
		$handle = @fopen($metaFile, 'r+b'); // intentionally @
		if (!$handle) {
			$handle = @fopen($metaFile, 'wb'); // intentionally @

			if (!$handle) {
				return FALSE;
			}
		}

		flock($handle, LOCK_EX);
		ftruncate($handle, 0);

		if ($this->writeData($meta, $data)) {
			$s = serialize($meta);
			$len = strlen($s);
			if ($len === fwrite($handle, $s, $len)) {
				fclose($handle);
				return TRUE;
			}
			ftruncate($handle, 0);
		}

		@unlink($meta['file']); // intentionally @
		@unlink($metaFile); // intentionally @

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
		$metaFile = $this->getMetaFile($key);
		$meta = $this->readMeta($metaFile, LOCK_EX);
		if (!$meta) return TRUE;

		ftruncate($meta['handle'], 0);
		@unlink($meta['file']); // intentionally @
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
		$tags = isset($conds['tags']) ? array_flip((array) $conds['tags']) : array();

		$priority = isset($conds['priority']) ? $conds['priority'] : -1;

		$all = !empty($conds['all']);

		$now = time();

		foreach (glob($this->base . '*.meta') as $metaFile)
		{
			if (!is_file($metaFile)) continue;

			do {
				$meta = $this->readMeta($metaFile, LOCK_SH);
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
			@unlink($meta['file']); // intentionally @
			@unlink($metaFile); // intentionally @
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

		$meta = stream_get_contents($handle);
		$meta = @unserialize($meta); // intentionally @
		if (!is_array($meta)) {
			fclose($handle);
			return NULL;
		}

		$meta['handle'] = $handle;
		return $meta;
	}



	/**
	 * Reads cache data from disk and closes meta file handle.
	 * @param  array
	 * @return mixed
	 */
	protected function readData($meta)
	{
		$data = @file_get_contents($meta['file']); // intentionally @
		fclose($meta['handle']);

		if (empty($meta['serialized'])) {
			return $data;
		} else {
			return @unserialize($data); // intentionally @
		}
	}



	/**
	 * Writes cache data to disk.
	 * @param  array
	 * @param  string
	 * @return bool
	 */
	protected function writeData($meta, $data)
	{
		return file_put_contents($meta['file'], $data) === strlen($data);
	}



	/**
	 * Returns file name.
	 * @param  string
	 * @return string
	 */
	protected function getMetaFile($key)
	{
		return $this->base . urlencode($key) . '.meta';
	}



	/**
	 * Returns file name.
	 * @param  string
	 * @return string
	 */
	protected function getDataFile($key)
	{
		return $this->base . urlencode($key) . '.data';
	}

}
