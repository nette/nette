<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette;

use Nette;



/**
 * Thread safe / atomic file manipulation. Stream safe://
 *
 * <code>
 * file_put_contents('safe://myfile.txt', $content);
 *
 * $content = file_get_contents('safe://myfile.txt');
 *
 * unlink('safe://myfile.txt');
 * </code>
 *
 * @author     David Grudl
 */
final class SafeStream
{
	/**
	 * Name of stream protocol - safe://
	 */
	const PROTOCOL = 'safe';

	/**
	 * Current file handle.
	 */
	private $handle;

	/**
	 * Renaming of temporary file.
	 */
	private $filePath;
	private $tempFile;

	/**
	 * Starting position in file (for appending).
	 */
	private $startPos = 0;

	/**
	 * Write-error detected?
	 */
	private $writeError = FALSE;



	/**
	 * Registers protocol 'safe://'.
	 * @return bool
	 */
	public static function register()
	{
		return stream_wrapper_register(self::PROTOCOL, __CLASS__);
	}



	/**
	 * Opens file.
	 * @param  string    file name with stream protocol
	 * @param  string    mode - see fopen()
	 * @param  int       STREAM_USE_PATH, STREAM_REPORT_ERRORS
	 * @param  string    full path
	 * @return bool      TRUE on success or FALSE on failure
	 */
	public function stream_open($path, $mode, $options, &$opened_path)
	{
		$path = substr($path, strlen(self::PROTOCOL)+3);  // trim protocol safe://

		$flag = trim($mode, 'rwax+');  // text | binary mode
		$mode = trim($mode, 'tb');     // mode
		$use_path = (bool) (STREAM_USE_PATH & $options); // use include_path?

		$append = FALSE;

		switch ($mode) {
		case 'r':
		case 'r+':
			// enter critical section: open and lock EXISTING file for reading/writing
			$handle = @fopen($path, $mode.$flag, $use_path); // intentionally @
			if (!$handle) return FALSE;
			if (flock($handle, $mode == 'r' ? LOCK_SH : LOCK_EX)) {
				$this->handle = $handle;
				return TRUE;
			}
			fclose($handle);
			return FALSE;

		case 'a':
		case 'a+': $append = TRUE;
		case 'w':
		case 'w+':
			// try enter critical section: open and lock EXISTING file for rewriting
			$handle = @fopen($path, 'r+'.$flag, $use_path); // intentionally @

			if ($handle) {
				if (flock($handle, LOCK_EX)) {
					if ($append) {
						fseek($handle, 0, SEEK_END);
						$this->startPos = ftell($handle);
					} else {
						ftruncate($handle, 0);
					}
					$this->handle = $handle;
					return TRUE;
				}
				fclose($handle);
			}
			// file doesn't exists, continue...
			$mode{0} = 'x'; // x || x+

		case 'x':
		case 'x+':
			if (file_exists($path)) return FALSE;

			// create temporary file in the same directory
			$tmp = '~~' . time() . '.tmp';

			// enter critical section: create temporary file
			$handle = @fopen($path . $tmp, $mode . $flag, $use_path); // intentionally @
			if ($handle) {
				if (flock($handle, LOCK_EX)) {
					$this->handle = $handle;
					if (!@rename($path . $tmp, $path)) { // intentionally @
						// rename later - for windows
						$this->tempFile = realpath($path . $tmp);
						$this->filePath = substr($this->tempFile, 0, -strlen($tmp));
					}
					return TRUE;
				}
				fclose($handle);
				unlink($path . $tmp);
			}
			return FALSE;

		default:
			trigger_error("Unsupported mode $mode", E_USER_WARNING);
			return FALSE;
		} // switch

	} // stream_open



	/**
	 * Closes file.
	 * @return void
	 */
	public function stream_close()
	{
		if ($this->writeError) {
			ftruncate($this->handle, $this->startPos);
		}

		fclose($this->handle);

		// are we working with temporary file?
		if ($this->tempFile) {
			// try to rename temp file, otherwise delete temp file
			if (!@rename($this->tempFile, $this->filePath)) { // intentionally @
				unlink($this->tempFile);
			}
		}
	}



	/**
	 * Reads up to length bytes from the file.
	 * @param  int    length
	 * @return string
	 */
	public function stream_read($length)
	{
		return fread($this->handle, $length);
	}



	/**
	 * Writes the string to the file.
	 * @param  string    data to write
	 * @return int      number of bytes that were successfully stored
	 */
	public function stream_write($data)
	{
		$len = strlen($data);
		$res = fwrite($this->handle, $data, $len);

		if ($res !== $len) { // disk full?
			$this->writeError = TRUE;
		}

		return $res;
	}



	/**
	 * Returns the position of the file.
	 * @return int
	 */
	public function stream_tell()
	{
		return ftell($this->handle);
	}



	/**
	 * Returns TRUE if the file pointer is at end-of-file.
	 * @return bool
	 */
	public function stream_eof()
	{
		return feof($this->handle);
	}



	/**
	 * Sets the file position indicator for the file.
	 * @param  int    position
	 * @param  int    see fseek()
	 * @return int   Return TRUE on success
	 */
	public function stream_seek($offset, $whence)
	{
		return fseek($this->handle, $offset, $whence) === 0; // ???
	}



	/**
	 * Gets information about a file referenced by $this->handle.
	 * @return array
	 */
	public function stream_stat()
	{
		return fstat($this->handle);
	}



	/**
	 * Gets information about a file referenced by filename.
	 * @param  string    file name
	 * @param  int       STREAM_URL_STAT_LINK, STREAM_URL_STAT_QUIET
	 * @return array
	 */
	public function url_stat($path, $flags)
	{
		// This is not thread safe
		$path = substr($path, strlen(self::PROTOCOL)+3);
		return ($flags & STREAM_URL_STAT_LINK) ? @lstat($path) : @stat($path); // intentionally @
	}



	/**
	 * Deletes a file.
	 * On Windows unlink is not allowed till file is opened
	 * @param  string    file name with stream protocol
	 * @return bool      TRUE on success or FALSE on failure
	 */
	public function unlink($path)
	{
		$path = substr($path, strlen(self::PROTOCOL)+3);
		return unlink($path);
	}

}
