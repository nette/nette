<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Web
 */

namespace Nette\Web;

use Nette;



/**
 * Access to a FTP server.
 *
 * <code>
 * $ftp = new Ftp;
 * $ftp->connect('ftp.example.com');
 * $ftp->login('anonymous', 'example@example.com');
 * $ftp->get('file.txt', 'README', Ftp::ASCII);
 * </code>
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Web
 */
class Ftp extends Nette\Object
{
	/**#@+ FTP constant alias */
	const ASCII = FTP_ASCII;
	const TEXT = FTP_TEXT;
	const BINARY = FTP_BINARY;
	const IMAGE = FTP_IMAGE;
	const TIMEOUT_SEC = FTP_TIMEOUT_SEC;
	const AUTOSEEK = FTP_AUTOSEEK;
	const AUTORESUME = FTP_AUTORESUME;
	const FAILED = FTP_FAILED;
	const FINISHED = FTP_FINISHED;
	const MOREDATA = FTP_MOREDATA;
	/**#@-*/

	/** @var resource */
	private $resource;

	/** @var array */
	private $state;



	/**
	 */
	public function __construct()
	{
		if (!extension_loaded('ftp')) {
			throw new \Exception("PHP extension FTP is not loaded.");
		}
	}



	/**
	 * Magic method (do not call directly).
	 * @param  string  method name
	 * @param  array   arguments
	 * @return mixed
	 * @throws \MemberAccessException
	 * @throws FtpException
	 */
	public function __call($name, $args)
	{
		$name = strtolower($name);
		$silent = strncmp($name, 'try', 3) === 0;
		$func = $silent ? substr($name, 3) : $name;
		static $aliases = array(
			'sslconnect' => 'ssl_connect',
			'getoption' => 'get_option',
			'setoption' => 'set_option',
			'nbcontinue' => 'nb_continue',
			'nbfget' => 'nb_fget',
			'nbfput' => 'nb_fput',
			'nbget' => 'nb_get',
			'nbput' => 'nb_put',
		);
		$func = 'ftp_' . (isset($aliases[$func]) ? $aliases[$func] : $func);

		if (!function_exists($func)) {
			return parent::__call($name, $args);
		}


		Nette\Tools::tryError();

		if ($func === 'ftp_connect' || $func === 'ftp_ssl_connect') {
			$this->state = array($name => $args);
			$this->resource = call_user_func_array($func, $args);
			$res = NULL;

		} elseif (!is_resource($this->resource)) {
			Nette\Tools::catchError($msg);
			throw new FtpException("Not connected to FTP server. Call connect() or ssl_connect() first.");

		} else {
			if ($func === 'ftp_login' || $func === 'ftp_pasv') {
				$this->state[$name] = $args;
			}

			array_unshift($args, $this->resource);
			$res = call_user_func_array($func, $args);

			if ($func === 'ftp_chdir' || $func === 'ftp_cdup') {
				$this->state['chdir'] = array(ftp_pwd($this->resource));
			}
		}

		if (Nette\Tools::catchError($msg) && !$silent) {
			throw new FtpException($msg);
		}

		return $res;
	}



	/**
	 * Reconnects to FTP server.
	 * @return void
	 */
	public function reconnect()
	{
		@ftp_close($this->resource); // intentionally @
		foreach ($this->state as $name => $args) {
			call_user_func_array(array($this, $name), $args);
		}
	}



	/**
	 * Checks if file or directory exists.
	 * @param  string
	 * @return bool
	 */
	public function fileExists($file)
	{
		return is_array($this->nlist($file));
	}



	/**
	 * Checks if directory exists.
	 * @param  string
	 * @return bool
	 */
	public function isDir($dir)
	{
		$current = $this->pwd();
		try {
			$this->chdir($dir);
		} catch (FtpException $e) {
		}
		$this->chdir($current);
		return empty($e);
	}



	/**
	 * Recursive creates directories.
	 * @param  string
	 * @return void
	 */
	public function mkDirRecursive($dir)
	{
		$parts = explode('/', $dir);
		$path = '';
		while (!empty($parts)) {
			$path .= array_shift($parts);
			try {
				if ($path !== '') $this->mkdir($path);
			} catch (FtpException $e) {
				if (!$this->isDir($path)) {
					throw new FtpException("Cannot create directory '$path'.");
				}
			}
			$path .= '/';
		}
	}

}



/**
 * FTP server exception.
 *
 * @package    Nette\Web
 */
class FtpException extends \Exception
{
}