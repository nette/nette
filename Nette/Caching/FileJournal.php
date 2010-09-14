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
 * File journal.
 *
 * fj structure
 *
 *     Header : ( Magic : int32
 *                SectionCount : int32
 *                Sections : SectionCount*( Name : int32
 *                                          Offset : int32
 *                                          KeyLength : int32
 *                                          KeyCount : int32
 *                                        )
 *                Padding : ... to 4096*byte
 *              )
 *     SectionCount*( Sections[i].KeyCount*( Key : Sections[i].KeyLength*byte
 *                                           ValueOffset : int32
 *                                           ValueLength : int32
 *                                         )
 *     Data : *byte
 *
 *
 * fj.log structure
 *
 *     *( Record : ( N : int32
 *                   Serialized : N*byte
 *                 )
 *      )
 *
 * @author     Jakub Kulhan
 */
class FileJournal extends Nette\Object implements ICacheJournal
{
	const
		MAGIC = 0x666a3030,// "fj00"
		FILE = 'fj',
		EXTNEW = '.new',
		EXTLOG = '.log',
		EXTLOGNEW = '.log.new',
		LOGMAXSIZE = 65536, // 64KiB
		INT32 = 4,
		TAGS = 0x74616773, // "tags"
		PRIORITY = 0x7072696f, // "prio"
		ENTRIES = 0x656e7473, // "ents"
		DELETE = 'd',
		ADD = 'a',
		CLEAN = 'c';

	/** @var array */
	private static $ops = array(
		self::ADD => self::DELETE,
		self::DELETE => self::ADD
	);

	/** @var string */
	private $file;

	/** @var resource */
	private $handle;

	/** @var int */
	private $mtime = 0;

	/** @var array */
	private $sections = array();

	/** @var resource */
	private $logHandle;

	/** @var bool */
	private $isLogNew = FALSE;

	/** @var array */
	private $logMerge = array();

	/** @var int */
	private $logMergeP = 0;

	/**
	 * Initalizes instance
	 * @param  string
	 */
	public function __construct($dir)
	{
		$this->file = $dir . '/' . self::FILE;
		$this->open();
	}



	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		if ($this->handle) {
			fclose($this->handle);
		}

		if ($this->logHandle) {
			fclose($this->logHandle);
		}
	}



	/**
	 * Reload.
	 */
	private function reload()
	{
		if (($mtime = @filemtime($this->file)) === FALSE) {
			$mtime = 0;
		}

		if ($this->mtime < $mtime) {
			fclose($this->handle);
			fclose($this->logHandle);
			$this->handle = $this->logHandle = NULL;
			$this->open();
		}

		$this->logMerge = $this->mergeLogFile($this->logHandle, $this->logMergeP, $this->logMerge);
	}



	/**
	 * Opens files.
	 */
	private function open()
	{
		$this->handle = $this->logHandle = NULL;
		$this->mtime = $this->logMergeP = 0;
		$this->sections = $this->logMerge = array();

		clearstatcache();
		if (($this->mtime = @filemtime($this->file)) === FALSE) {
			$this->mtime = 0;
		}

		$tries = 3;
		do {
			if (!$tries--) {
				throw new \InvalidStateException('Cannot open journal file ' . $this->file . '.');
			}

			if (!($this->handle = @fopen($this->file, 'rb'))) { // intentionally @
				// file is not present
				$this->handle = NULL;

			} else {
				list(,$magic, $sectionCount) = unpack('N2', fread($this->handle, 2 * self::INT32));

				if ($magic !== self::MAGIC) {
					fclose($this->handle);
					throw new \InvalidStateException('Malformed journal file ' . $this->file . '.');
				}

				for ($i = 0; $i < $sectionCount; ++$i) {
					list(,$name, $offset, $keyLength, $keyCount) =
						unpack('N4', fread($this->handle, 4 * self::INT32));

					$this->sections[$name] = (object) array(
						'offset' => $offset,
						'keyLength' => $keyLength,
						'keyCount' => $keyCount,
					);
				}
			}

			// what if it was rebuilt in the meantime?
			clearstatcache();
			if (($mtime = @filemtime($this->file)) === FALSE) {
				$mtime = 0;
			}
		} while ($this->mtime < $mtime);


		if (!($this->logHandle = @fopen($logfile = $this->file . self::EXTLOG, 'a+b'))) { // intentionally @
			throw new \InvalidStateException('Cannot open logfile ' . $logfile . ' for journal.');
		}

		$doMergeFirst = FALSE;
		$openNewLog = FALSE;
		$reopen = FALSE;
		if (flock($this->logHandle, LOCK_SH | LOCK_NB)) {
			if (file_exists($logfile = $this->file . self::EXTLOGNEW)) {
				if (($logmtime = @filemtime($this->file . self::EXTLOG)) === FALSE) {
					throw new \InvalidStateException('Cannot determine mtime of logfile ' . $this->file . self::EXTLOG . '.');
				}

				if ($logmtime < $this->mtime) {
					// rebuild completed, but log not removed
					fclose($this->logHandle);
					if (!@rename($this->file . self::EXTLOGNEW, $this->file . self::EXTLOG)) { // intentionally @
						clearstatcache();
						if (!file_exists($this->file . self::EXTLOGNEW)) {
							// someone else renamed it
							$reopen = TRUE;
						} else {
							// cannot rename and still exists -- open it
							$openNewLog = TRUE;
						}
					} else {
						// success fully renamed
						$reopen = TRUE;
					}

				} else {
					// rebuild not completed
					if (!$this->rebuild()) {
						$doMergeFirst = TRUE;
						$openNewLog = TRUE;

					} // else file already reopened by rebuild()
				}

			} // else log opened, no new log, everything ok

			// instance retains shared lock, so nobody can rebuild and change opened
			// files without us knowing

		} else {
			// being rebuilt, open new log
			$doMergeFirst = TRUE;
			$openNewLog = TRUE;
		}

		if ($reopen && $openNewLog) {
			throw new \LogicException('Something bad with algorithm.');
		}

		if ($doMergeFirst) {
			$this->logMerge = $this->mergeLogFile($this->logHandle, 0);
		}

		if ($reopen) {
			fclose($this->logHandle);
			if (!($this->logHandle = @fopen($logfile = $this->file . self::EXTLOG, 'a+b'))) {
				throw new \InvalidStateException('Cannot open logfile ' . $logfile . '.');
			}

			if (!flock($this->logHandle, LOCK_SH)) {
				throw new \InvalidStateException('Cannot acquite shared lock on log.');
			}
		}

		if ($openNewLog) {
			fclose($this->logHandle);
			if (!($this->logHandle = @fopen($logfile = $this->file . self::EXTLOGNEW, 'a+b'))) { // intentionally @
				throw new \InvalidStateException('Cannot open logfile ' . $logfile . '.');
			}

			$this->isLogNew = TRUE;
		}

		$this->logMerge = $this->mergeLogFile($this->logHandle, 0, $this->logMerge);
		$this->logMergeP = ftell($this->logHandle);

		// empty-log Windows fix
		if ($this->logMergeP === 0) {
			if (!flock($this->logHandle, LOCK_EX)) {
				throw new \InvalidStateException('Cannot acquite exclusive lock on log.');
			}

			$data = serialize(array());
			$data = pack('N', strlen($data)) . $data;
			$written = fwrite($this->logHandle, $data);
			if ($written === FALSE || $written !== strlen($data)) {
				throw new \InvalidStateException('Cannot write empty packet to log.');
			}

			if (!flock($this->logHandle, LOCK_SH)) {
				throw new \InvalidStateException('Cannot acquite shared lock on log.');
			}
		}
	}



	/**
	 * Writes entry information into the journal.
	 * @param  string
	 * @param  array
	 * @return void
	 */
	public function write($key, array $dependencies)
	{
		$log = array();
		$delete = $this->get(self::ENTRIES, $key);

		if ($delete !== NULL && isset($delete[$key])) {
			foreach ($delete[$key] as $id) {
				list($sectionName, $k) = explode(':', $id, 2);
				$sectionName = intval($sectionName);
				if (!isset($log[$sectionName])) {
					$log[$sectionName] = array();
				}

				if (!isset($log[$sectionName][self::DELETE])) {
					$log[$sectionName][self::DELETE] = array();
				}

				$log[$sectionName][self::DELETE][$k][] = $key;
			}
		}

		if (!empty($dependencies[Cache::TAGS])) {
			if (!isset($log[self::TAGS])) {
				$log[self::TAGS] = array();
			}

			if (!isset($log[self::TAGS][self::ADD])) {
				$log[self::TAGS][self::ADD] = array();
			}

			foreach ((array) $dependencies[Cache::TAGS] as $tag) {
				$log[self::TAGS][self::ADD][$tag] = (array) $key;
			}
		}

		if (!empty($dependencies[Cache::PRIORITY])) {
			if (!isset($log[self::PRIORITY])) {
				$log[self::PRIORITY] = array();
			}

			if (!isset($log[self::PRIORITY][self::ADD])) {
				$log[self::PRIORITY][self::ADD] = array();
			}

			$log[self::PRIORITY][self::ADD][sprintf('%010u', (int) $dependencies[Cache::PRIORITY])] = (array) $key;
		}

		if (empty($log)) {
			return ;
		}

		$entriesSection = array(self::ADD => array());

		foreach ($log as $sectionName => $section) {
			if (!isset($section[self::ADD])) {
				continue;
			}

			foreach ($section[self::ADD] as $k => $_) {
				$entriesSection[self::ADD][$key][] = "$sectionName:$k";
			}
		}

		$entriesSection[self::ADD][$key][] = self::ENTRIES . ':' . $key;
		$log[self::ENTRIES] = $entriesSection;

		$this->log($log);
	}



	/**
	 * Adds item to log.
	 * @param  array
	 * @return bool
	 */
	private function log(array $data)
	{
		$data = $this->mergeLogRecords(array(), $data);

		// let's assume the data won't be larger than filesystem block size, so it
		// should be atomic
		$data = serialize($data);
		$data = pack('N', strlen($data)) . $data;

		$written = fwrite($this->logHandle, $data);
		if ($written === FALSE || $written !== strlen($data)) {
			throw new \InvalidStateException('Cannot write to log.');
		}


		// rebuild main journal file if needed
		if (!$this->isLogNew) {
			fseek($this->logHandle, 0, SEEK_END);
			$size = ftell($this->logHandle);
			if ($size > self::LOGMAXSIZE) {
				$this->rebuild();
			}
		}

		return TRUE;
	}



	/**
	 * Rebuilds main journal file.
	 * @return bool
	 */
	private function rebuild()
	{
		if (!flock($this->logHandle, LOCK_EX | LOCK_NB)) {
			// already being rebuilt
			return TRUE;
		}

		if (!($newhandle = @fopen($this->file . self::EXTNEW, 'wb'))) { // intentionally @
			flock($this->logHandle, LOCK_UN);
			return FALSE;
		}

		// get modifications
		$merged = $this->mergeLogFile($this->logHandle);

		$sections = array_unique(
			array_merge(array_keys($this->sections), array_keys($merged)),
			SORT_NUMERIC
		);
		sort($sections);

		// determine new sections
		$offset = 4096; // 4 KiB for header
		$newsections = array();

		foreach ($sections as $section) {
			$maxKeyLength = 0;
			$keyCount = 0;

			if (isset($this->sections[$section])) {
				$maxKeyLength = $this->sections[$section]->keyLength;
				$keyCount = $this->sections[$section]->keyCount;
			}

			if (isset($merged[$section][self::ADD])) {
				foreach ($merged[$section][self::ADD] as $k => $_) {
					if (($len = strlen((string) $k)) > $maxKeyLength) {
						$maxKeyLength = $len;
					}

					$keyCount++; // let's assume that everything to add is not already in there
				}
			}

			$newsections[$section] = (object) array(
				'keyLength' => $maxKeyLength,
				'keyCount' => $keyCount,
				'offset' => $offset,
			);

			$offset += $keyCount * ($maxKeyLength + 2 * self::INT32);
		}

		$dataOffset = $offset;
		$dataWrite = array();
		$clean = isset($merged[self::CLEAN]);
		unset($merged[self::CLEAN]);

		// copy from old to new
		foreach ($sections as $section) {
			fseek($newhandle, $newsections[$section]->offset, SEEK_SET);

			$pack = 'a' . $newsections[$section]->keyLength . 'NN';
			$realKeyCount = 0;

			foreach (self::$ops as $op) {
				if (isset($merged[$section][$op])) {
					reset($merged[$section][$op]);
				}
			}

			if ($this->handle && isset($this->sections[$section]) && !$clean) {
				$unpack = 'a' . $this->sections[$section]->keyLength . 'key/NvalueOffset/NvalueLength';
				$recordSize = $this->sections[$section]->keyLength + 2 * self::INT32;
				$batchSize = intval(65536 / $recordSize); // load at most 64 KiB in one batch
				$i = 0;

				while ($i < $this->sections[$section]->keyCount) {
					// load batch
					fseek($this->handle, $this->sections[$section]->offset + $i * $recordSize, SEEK_SET);
					$size = min($batchSize, $this->sections[$section]->keyCount - $i);
					$data = stream_get_contents($this->handle, $size * $recordSize);

					if (!($data !== FALSE && strlen($data) === $size * $recordSize)) {
						flock($this->logHandle, LOCK_UN);
						fclose($newhandle);
						return FALSE;
					}

					// process batch
					for ($j = 0; $j < $size && $i < $this->sections[$section]->keyCount; ++$j, ++$i) {
						$record = (object) unpack($unpack, substr($data, $j * $recordSize, $recordSize));
						$value = NULL;

						// check for deletes
						if (isset($merged[$section][self::DELETE])) {

							// skip already deleted
							while (current($merged[$section][self::DELETE]) &&
								strcmp(key($merged[$section][self::DELETE]), $record->key) < 0)
							{
								next($merged[$section][self::DELETE]);
							}

							// alter value of this key?
							if (strcmp(key($merged[$section][self::DELETE]), $record->key) === 0) {
								fseek($this->handle, $record->valueOffset, SEEK_SET);
								$value = @unserialize(fread($this->handle, $record->valueLength)); // intentionally @

								if ($value === FALSE) {
									flock($this->logHandle, LOCK_UN);
									fclose($newhandle);
									return FALSE;
								}

								$value = array_flip($value);
								foreach (current($merged[$section][self::DELETE]) as $delete) {
									unset($value[$delete]);
								}
								$value = array_keys($value);

								next($merged[$section][self::DELETE]);
							}
						}

						// additions
						if (isset($merged[$section][self::ADD])) {

							// add not added yet
							while (current($merged[$section][self::ADD]) &&
								strcmp(key($merged[$section][self::ADD]), $record->key) < 0)
							{
								$dataWrite[] = ($serialized = serialize(current($merged[$section][self::ADD])));
								$packed = pack($pack, key($merged[$section][self::ADD]), $dataOffset, strlen($serialized));

								if (!$this->writeAll($newhandle, $packed)) {
									flock($this->logHandle, LOCK_UN);
									fclose($newhandle);
									return FALSE;
								}

								$realKeyCount++;
								$dataOffset += strlen($serialized);
								next($merged[$section][self::ADD]);
							}

							// alter value of this key?
							if (strcmp(key($merged[$section][self::ADD]), $record->key) === 0) {

								// if value hasn't been already loaded, load it
								if ($value === NULL) {
									$value = $this->loadValue($this->handle, $record->valueOffset, $record->valueLength);
								}

								if ($value === NULL) {
									flock($this->logHandle, LOCK_UN);
									fclose($newhandle);
									return FALSE;
								}

								$value = array_unique(array_merge(
									$value,
									current($merged[$section][self::ADD])
								));

								sort($value);

								next($merged[$section][self::ADD]);
							}
						}


						if (is_array($value) && !empty($value) || $value === NULL) {
							if ($value !== NULL) {
								$dataWrite[] = ($serialized = serialize($value));
								$newValueLength = strlen($serialized);

							} else {
								$dataWrite[] = array($record->valueOffset, $record->valueLength);
								$newValueLength = $record->valueLength;
							}

							if (!$this->writeAll($newhandle, pack($pack, $record->key, $dataOffset, $newValueLength))) {
								flock($this->logHandle, LOCK_UN);
								fclose($newhandle);
								return FALSE;
							}

							$realKeyCount++;
							$dataOffset += $newValueLength;
						}
					}
				}
			}

			while (isset($merged[$section][self::ADD]) && current($merged[$section][self::ADD])) {
				$dataWrite[] = ($serialized = serialize(current($merged[$section][self::ADD])));
				$valueLength = strlen($serialized);
				$packed = pack($pack, key($merged[$section][self::ADD]), $dataOffset, $valueLength);

				if (!$this->writeAll($newhandle, $packed)) {
					flock($this->logHandle, LOCK_UN);
					fclose($newhandle);
					return FALSE;
				}

				$realKeyCount++;
				$dataOffset += $valueLength;
				next($merged[$section][self::ADD]);
			}

			$newsections[$section]->keyCount = $realKeyCount;

			if ($realKeyCount < 1) {
				unset($newsections[$section]);
			}
		}

		// write header
		fseek($newhandle, 0, SEEK_SET);
		$data = pack('NN', self::MAGIC, count($newsections));
		foreach ($newsections as $name => $section) {
			$data .= pack('NNNN', $name, $section->offset, $section->keyLength, $section->keyCount);
		}

		if (!$this->writeAll($newhandle, $data)) {
			flock($this->logHandle, LOCK_UN);
			fclose($newhandle);
			return FALSE;
		}

		// write values data
		fseek($newhandle, $offset, SEEK_SET);
		reset($dataWrite);

		while (!empty($dataWrite)) {
			$data = array_shift($dataWrite);
			if (is_string($data)) {

				// join sequential writes
				while (is_string(current($dataWrite))) {
					$data .= array_shift($dataWrite);
				}

				if (!$this->writeAll($newhandle, $data)) {
					flock($this->logHandle, LOCK_UN);
					fclose($newhandle);
					return FALSE;
				}
			} else {
				if (!is_array($data)) {
					throw new \LogicException('Something bad with algorithm, it has to be array.');
				}

				list($readOffset, $readLength) = $data;

				// join sequential reads
				while (!empty($dataWrite) && is_array(current($dataWrite))) {
					list($nextReadOffset, $nextReadLength) = current($dataWrite);

					if ($readOffset + $readLength !== $nextReadOffset) {
						break;
					}

					$readLength += $nextReadLength;
					array_shift($dataWrite);
				}

				fseek($this->handle, $readOffset, SEEK_SET);

				while (($readLength -=
					stream_copy_to_stream($this->handle, $newhandle, $readLength)) > 0);
			}
		}


		// final renaming etc.

		fflush($newhandle); // I want fsync(2), dammit!
		fclose($newhandle);
		$newhandle = NULL;

		if ($this->handle) {
			fclose($this->handle);
			$this->handle = NULL;
		}

		if (!@rename($this->file . self::EXTNEW, $this->file)) { // intentionally @
			flock($this->logHandle, LOCK_UN);
			return FALSE;
		}

		ftruncate($this->logHandle, 4 + strlen(serialize(array()))); // retain empty record at beginning
		flock($this->logHandle, LOCK_UN);
		fclose($this->logHandle);

		if (!@rename($this->file . self::EXTLOGNEW, $this->file . self::EXTLOG) && // intentionally @
			file_exists($this->file . self::EXTLOGNEW))
		{
			$this->isLogNew = TRUE;
			$logfile = $this->file . self::EXTLOGNEW;

		} else {
			// someone else already renamed it, or it wasn't created by anyone else yet
			$logfile = $this->file . self::EXTLOG;
		}

		if (!($this->logHandle = @fopen($logfile, 'a+b'))) { // intentionally @
			throw new \InvalidStateException('Cannot reopen logfile ' . $logfile . '.');
		}

		$this->logMerge = array();
		$this->logMergeP = 0;

		if (!($this->handle = @fopen($this->file, 'rb'))) {
			throw new \InvalidStateException('Cannot reopen file ' . $this->file . '.');
		}

		clearstatcache();
		$this->mtime = (int) @filemtime($this->file);
		$this->sections = $newsections;

		return TRUE;
	}



	/**
	 * Writes all data to handle.
	 * @param  resource
	 * @param  string
	 * @return bool TRUE if everything is ok
	 */
	private function writeAll($handle, $data)
	{
		$bytesLeft = strlen($data);

		while ($bytesLeft > 0) {
			$written = fwrite($handle, substr($data, strlen($data) - $bytesLeft));
			if ($written === FALSE) {
				return FALSE;
			}
			$bytesLeft -= $written;
		}

		return TRUE;
	}



	/**
	 * Loads one value from given handle.
	 * @param  resource
	 * @param  int
	 * @param  int
	 * @return array|NULL
	 */
	private function loadValue($handle, $offset, $length)
	{
		fseek($handle, $offset, SEEK_SET);
		$data = '';
		$bytesLeft = $length;

		while ($bytesLeft > 0) {
			$read = fread($handle, $bytesLeft);
			if ($read === FALSE) {
				return NULL;
			}

			$data .= $read;
			$bytesLeft -= strlen($read);
		}

		$value = @unserialize($data); // intentionally @

		if ($value === FALSE) {
			return NULL;
		}

		return $value;
	}



	/**
	 * Merges all records in given handle into one record.
	 * @param  resource
	 * @return array|NULL
	 */
	private function mergeLogFile($handle, $startp = 0, $merged = array())
	{
		fseek($handle, $startp, SEEK_SET);

		while (!feof($handle) && strlen($data = fread($handle, self::INT32)) === self::INT32) {
			list(,$size) = unpack('N', $data);
			$data = @unserialize(fread($handle, $size)); // intentionally @

			if ($data === FALSE) {
				continue; // skip record
			}

			$merged = $this->mergeLogRecords($merged, $data);
		}

		ksort($merged);

		return $merged;
	}



	/**
	 * Merges log records.
	 * @param  array
	 * @param  array
	 * @return array
	 */
	private function mergeLogRecords(array $a, array $b)
	{
		$clean = isset($a[self::CLEAN]);
		unset($a[self::CLEAN], $b[self::CLEAN]);

		if (isset($b[self::CLEAN])) {
			return $b;
		}

		foreach ($b as $section => $data) {
			if (!isset($a[$section])) {
				$a[$section] = array();
			}

			foreach (self::$ops as $op) {
				if (!isset($data[$op])) {
					continue;
				}

				if (!isset($a[$section][$op])) {
					$a[$section][$op] = array();
				}

				foreach ($data[$op] as $k => $v) {
					if (!isset($a[$section][$op][$k])) {
						$a[$section][$op][$k] = array();
					}

					$a[$section][$op][$k] = array_unique(array_merge(
						$a[$section][$op][$k],
						$v
					));

					if (isset($a[$section][self::$ops[$op]][$k])) {
						$a[$section][self::$ops[$op]][$k] =
							array_flip($a[$section][self::$ops[$op]][$k]);

						foreach ($v as $unsetk) {
							unset($a[$section][self::$ops[$op]][$k][$unsetk]);
						}

						$a[$section][self::$ops[$op]][$k] =
							array_keys($a[$section][self::$ops[$op]][$k]);
					}
				}
			}

			foreach (self::$ops as $op) {
				if (!isset($a[$section][$op])) {
					continue;
				}

				foreach ($a[$section][$op] as $k => $v) {
					if (empty($v)) {
						unset($a[$section][$op][$k]);
						continue;
					}

					sort($a[$section][$op][$k]);
				}

				if (empty($a[$section][$op])) {
					unset($a[$section][$op]);
					continue;
				}

				ksort($a[$section][$op]);
			}
		}

		if ($clean) {
			$a[self::CLEAN] = TRUE;
		}

		return $a;
	}



	/**
	 * Cleans entries from journal.
	 * @param  array
	 * @return array of removed items or NULL when performing a full cleanup
	 */
	public function clean(array $conditions)
	{
		if (!empty($conditions[Cache::ALL])) {
			$this->log(array(self::CLEAN => TRUE));
			return NULL;

		} else {
			$log = array();
			$entries = array();

			if (!empty($conditions[Cache::TAGS])) {
				$tagEntries = array();

				foreach ((array) $conditions[Cache::TAGS] as $tag) {
					$tagEntries = array_merge($tagEntries, $tagEntry = $this->get(self::TAGS, $tag));

					if (isset($tagEntry[$tag])) {
						foreach ($tagEntry[$tag] as $entry) {
							$entries[] = $entry;
						}
					}
				}

				if (!empty($tagEntries)) {
					if (!isset($log[self::TAGS])) {
						$log[self::TAGS] = array();
					}

					$log[self::TAGS][self::DELETE] = $tagEntries;
				}
			}

			if (isset($conditions[Cache::PRIORITY])) {
				$priorityEntries = $this->getLte(self::PRIORITY, sprintf('%010u', (int) $conditions[Cache::PRIORITY]));
				foreach ($priorityEntries as $priorityEntry) {
					foreach ($priorityEntry as $entry) {
						$entries[] = $entry;
					}
				}

				if (!empty($priorityEntries)) {
					if (!isset($log[self::PRIORITY])) {
						$log[self::PRIORITY] = array();
					}

					$log[self::PRIORITY][self::DELETE] = $priorityEntries;
				}
			}

			if (!empty($log)) {
				if (!$this->log($log)) {
					return array();
				}
			}

			return array_values(array_unique($entries));
		}
	}



	/**
	 * Gets value by section and key.
	 * @param  int
	 * @param  string
	 * @return array
	 */
	private function get($section, $key)
	{
		$this->reload();

		$ret = $this->logMerge;

		if (!isset($ret[self::CLEAN])) {
			list($offset, $record) = $this->lowerBound($section, $key);

			if ($offset !== -1 && $record->key === $key && !isset($ret[self::CLEAN])) {
				$entries = $this->loadValue($this->handle, $record->valueOffset, $record->valueLength);

				$ret = $this->mergeLogRecords(
					array($section => array(self::ADD => array($key => $entries))),
					$ret
				);
			}
		}

		return isset($ret[$section][self::ADD][$key])
			? array($key => $ret[$section][self::ADD][$key])
			: array();
	}



	/**
	 * Gets values by section where key is less than or equal given key.
	 * @param  int
	 * @param  string
	 * @return array
	 */
	private function getLte($section, $key)
	{
		$this->reload();
		$ret = array();

		if (!isset($this->logMerge[self::CLEAN])) {
			list($offset, $record) = $this->lowerBound($section, $key);

			if ($offset !== -1) {
				$unpack = 'a' . $this->sections[$section]->keyLength . 'key/NvalueOffset/NvalueLength';
				$recordSize = $this->sections[$section]->keyLength + 2 * self::INT32;
				$batchSize = intval(65536 / $recordSize);
				$i = 0;
				$count = ($offset - $this->sections[$section]->offset) / $recordSize;

				if ($record->key === $key) {
					$count += 1;
				}

				while ($i < $count) {
					// load batch
					fseek($this->handle, $this->sections[$section]->offset + $i * $recordSize, SEEK_SET);
					$size = min($batchSize, $count - $i);
					$data = stream_get_contents($this->handle, $size * $recordSize);

					if (!($data !== FALSE && strlen($data) === $size * $recordSize)) {
						return NULL;
					}

					// process batch
					for ($j = 0; $j < $size && $i < $count; ++$j, ++$i) {
						$record = (object) unpack($unpack, substr($data, $j * $recordSize, $recordSize));
						$ret[$record->key] = $this->loadValue($this->handle, $record->valueOffset, $record->valueLength);

						if ($ret[$record->key] === NULL) {
							unset($ret[$record->key]);
						}
					}
				}
			}
		}

		if (isset($this->logMerge[$section][self::DELETE])) {
			$ret = $this->mergeLogRecords(
				array($section => array(self::DELETE => $this->logMerge[$section][self::DELETE])),
				array($section => array(self::ADD => $ret))
			);

			if (!isset($ret[$section][self::ADD])) {
				$ret = array();

			} else {
				$ret = $ret[$section][self::ADD];
			}
		}

		if (isset($this->logMerge[$section][self::ADD])) {
			foreach ($this->logMerge[$section][self::ADD] as $k => $v) {
				if (strcmp($k, $key) > 0) {
					continue;
				}

				if (!isset($ret[$k])) {
					$ret[$k] = array();
				}

				$ret[$k] = array_values(array_unique(array_merge($ret[$k], $v)));
			}
		}

		return $ret;
	}



	/**
	 * Finds value by section and key.
	 * @param  int
	 * @param  string
	 * @return (int,object) (-1,-1,-1) on failure, (upperOffset,0,0) if not found,
	 *                      (offset,valueOffset,valueLength) otherwise
	 */
	private function lowerBound($section, $key)
	{
		if (!isset($this->sections[$section])) {
			return array(-1, NULL);
		}

		$l = 0;
		$r = $this->sections[$section]->keyCount;
		$unpack = 'a' . $this->sections[$section]->keyLength . 'key/NvalueOffset/NvalueLength';
		$recordSize = $this->sections[$section]->keyLength + 2 * self::INT32;

		while ($l < $r) {
			$m = intval(($l + $r) / 2);
			fseek($this->handle, $this->sections[$section]->offset + $m * $recordSize);
			$data = stream_get_contents($this->handle, $recordSize);

			if (!($data !== FALSE && strlen($data) === $recordSize)) {
				return array(-1, NULL);
			}

			$record = (object) unpack($unpack, $data);

			if (strcmp($record->key, $key) < 0) {
				$l = $m + 1;
			} else {
				$r = $m;
			}
		}

		fseek($this->handle, $this->sections[$section]->offset + $l * $recordSize);
		$data = stream_get_contents($this->handle, $recordSize);

		if (!($data !== FALSE && strlen($data) === $recordSize)) {
			return array(-1, NULL);
		}

		$record = (object) unpack($unpack, $data);

		return array(
			$this->sections[$section]->offset + $l * $recordSize,
			$record,
		);
	}
}
