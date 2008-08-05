<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette::Web
 * @version    $Id$
 */

/*namespace Nette::Web;*/



/**
 * Provides access to session namespaces as well as session settings and management methods.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Web
 */
final class Session
{
	/** @var array of SessionNamespace  registry of singleton instances */
	private static $instances = array();

	/** @var callback  Validation key generator */
	public static $verifyKeyGenerator = array(__CLASS__, 'getVerifyKey');

	/** @var bool Has been session started? */
	private static $started = FALSE;

	/** @var bool  Is required session id regeneration? */
	private static $regenerationNeeded;

	/** @var bool  Is reset needed? */
	private static $resetNeeded = TRUE;



	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new /*::*/LogicException("Cannot instantiate static class " . get_class($this));
	}



	/********************* session management ****************d*g**/



	/**
	 * Starts and initializes session data.
	 * @throws SessionException
	 * @return void
	 */
	public static function start()
	{
		// already started?
		if (defined('SID')) {
			throw new SessionException('A session had already been started by session.auto-start or session_start().');
		}
		if (self::$started) self::checkHeaders();

		// session configuration
		if (self::$resetNeeded) self::reset();

		/*Nette::*/Tools::tryError();
		session_start();
		if (/*Nette::*/Tools::catchError($msg)) {
			session_write_close(); // this is needed
			throw new SessionException($msg);
		}

		self::$started = TRUE;
		if (self::$regenerationNeeded) {
			self::regenerateId();
		}


		/*
		nette: __NT
		data:  __NS->namespace->variables->...
		meta:  __NM->namespace->EXP->variables
		*/

		// additional protection against Session Hijacking & Fixation
		if (self::$verifyKeyGenerator) {
			$key = call_user_func(self::$verifyKeyGenerator);
			$key = NULL; // debug
		} else {
			$key = NULL;
		}

		if (empty($_SESSION)) { // new session
			$_SESSION = array();
			$_SESSION['__NT']['COUNTER'] = 0;
			$_SESSION['__NT']['VERIFY'] = $key;

		} else {
			$saved = & $_SESSION['__NT']['VERIFY'];
			if ($saved === $key) { // verified
				$_SESSION['__NT']['COUNTER']++;

			} else { // session attack?
				session_regenerate_id(TRUE);
				$_SESSION = array();
				$_SESSION['__NT']['COUNTER'] = 0;
				$_SESSION['__NT']['VERIFY'] = $key;
			}
		}


		// process meta metadata
		if (isset($_SESSION['__NM'])) {
			$now = time();

			// expire namespace variables
			foreach ($_SESSION['__NM'] as $namespace => $metadata) {
				if (isset($metadata['EXP'])) {
					foreach ($metadata['EXP'] as $variable => $time) {
						if ($now > $time) {
							if ($variable === '') { // expire whole namespace
								unset($_SESSION['__NM'][$namespace], $_SESSION['__NS'][$namespace]);
								continue 2;
							}
							unset($_SESSION['__NS'][$namespace][$variable],
								$_SESSION['__NM'][$namespace]['EXP'][$variable]);
						}
					}
				}
			}
		}

		self::clean();
	}



	/**
	 * Has been session started?
	 * @return bool
	 */
	public static function isStarted()
	{
		return self::$started;
	}



	/**
	 * Ends the current session and store session data.
	 * @return void
	 */
	public static function close()
	{
		if (self::$started) {
			session_write_close();
			self::$started = FALSE;
		}
	}



	/**
	 * Destroys all data registered to a session.
	 * @param  bool  remove the session cookie? Defaults to TRUE
	 * @return void
	 */
	public static function destroy($removeCookie = TRUE)
	{
		if (!self::$started) {
			throw new SessionException('Session is not started.');
		}

		session_destroy();
		$_SESSION = NULL;
		self::$started = FALSE;

		if ($removeCookie) {
			self::checkHeaders();
			$params = session_get_cookie_params();
			setcookie(
				session_name(),
				FALSE,
				254400000,
				$params['path'],
				$params['domain'],
				$params['secure']
			);
		}
	}



	/**
	 * Does session exists for the current request?
	 * @return bool
	 */
	public static function exists()
	{
		return isset($_COOKIE[session_name()]);
	}



	/**
	 * Regenerates the session id.
	 * @throws SessionException
	 * @return void
	 */
	public static function regenerateId()
	{
		if (self::$started) {
			self::checkHeaders();
			session_regenerate_id(TRUE);

		} else {
			self::$regenerationNeeded = TRUE;
		}
	}



	/**
	 * Sets the session id to a user specified one.
	 * @throws SessionException
	 * @param  string $id
	 * @return void
	 */
	public static function setId($id)
	{
		if (defined('SID')) {
			throw new SessionException('A session had already been started - the session id must be set first.');
		}

		if (!is_string($id) || $id === '') {
			throw new /*::*/InvalidArgumentException('You must provide a non-empty string as a session id.');
		}

		session_id($id);
	}



	/**
	 * Returns the current session id.
	 * @return string
	 */
	public static function getId()
	{
		return session_id();
	}



	/**
	 * Generates key as protection against Session Hijacking & Fixation.
	 * @return string
	 */
	private static function getVerifyKey()
	{
		$list = array(
			'HTTP_ACCEPT_CHARSET', 'HTTP_ACCEPT_ENCODING',
			'HTTP_ACCEPT_LANGUAGE', 'HTTP_USER_AGENT',
		);

		$key = array();
		foreach ($list as $item) {
			if (isset($_SERVER[$item])) $key[] = $_SERVER[$item];
		}
		return md5(implode("\0", $key));
	}



	/********************* namespaces management ****************d*g**/



	/**
	 * Returns instance of session namespace.
	 * @param  string
	 * @return SessionNamespace
	 * @throws ::InvalidArgumentException
	 */
	public static function getNamespace($name = 'default')
	{
		if (!is_string($name) || $name === '') {
			throw new /*::*/InvalidArgumentException('Session namespace must be a non-empty string.');
		}

		if (!self::$started) {
			self::start();
		}

		if (!isset(self::$instances[$name])) {
			self::$instances[$name] = new SessionNamespace($_SESSION['__NS'][$name], $_SESSION['__NM'][$name]);
		}

		return self::$instances[$name];
	}



	/**
	 * Checks if a namespace exists.
	 *
	 * @param  string
	 * @throws SessionException
	 * @return bool
	 */
	public static function hasNamespace($namespace)
	{
		if (!self::$started) {
			throw new SessionException('Session is not started.');
		}

		return isset($_SESSION['__NS'][$namespace]);
	}



	/**
	 * Iteration over all namespaces.
	 * @throws SessionException
	 * @return ::ArrayIterator
	 */
	public static function getIterator()
	{
		if (!self::$started) {
			throw new SessionException('Session is not started.');
		}

		if (isset($_SESSION['__NS'])) {
			return new /*::*/ArrayIterator(array_keys($_SESSION['__NS']));
		} else {
			return new /*::*/ArrayIterator;
		}
	}



	/**
	 * Cleans and minimizes meta structures.
	 * @return void
	 */
	public static function clean()
	{
		if (!self::$started) {
			throw new SessionException('Session is not started.');
		}

		if (isset($_SESSION['__NM']) && is_array($_SESSION['__NM'])) {
			foreach ($_SESSION['__NM'] as $name => $foo) {
				if (empty($_SESSION['__NM'][$name]['EXP'])) {
					unset($_SESSION['__NM'][$name]['EXP']);
				}

				if (empty($_SESSION['__NM'][$name])) {
					unset($_SESSION['__NM'][$name]);
				}
			}
		}

		if (empty($_SESSION['__NM'])) {
			unset($_SESSION['__NM']);
		}

		if (empty($_SESSION['__NS'])) {
			unset($_SESSION['__NS']);
		}
	}



	/********************* configuration ****************d*g**/



	/**
	 * Configure session.
	 * @param  Config  configuration
	 * @return void
	 */
	public static function configure(Config $config)
	{
		// $config = /*Nette::*/Environment::getConfig(__CLASS__);
		// TODO: implement!
	}



	/**
	 * Resets session configuration.
	 * @return void
	 */
	public static function reset()
	{
		self::$resetNeeded = FALSE;
		// security
		ini_set('session.referer_check', '');   // default "" (PHP referer checking is invalid; disable it)
		ini_set('session.use_cookies', 1);      // default "1" (yes, use only cookies!)
		ini_set('session.use_only_cookies', 1); // default "1" (yes, use only cookies!)
		ini_set('session.use_trans_sid', 0);    // default "0" (no! use only cookies!)

		// cookie
		self::setCookieParams('/', '', '');
		self::setTimeout(0);
		// ini_set('session.hash_function', ?); // default "0"
		// ini_set('session.hash_bits_per_character', ?);  // default "4"
		// session_cache_limiter(?);    // default "nocache"
		// session_cache_expire(?);     // default "180"
	}



	/**
	 * Sets the amount of time in seconds allowed between.
	 * requests before the session will be terminated.
	 * @param  int
	 * @return void
	 */
	public static function setTimeout($seconds)
	{
		if (self::$resetNeeded) self::reset();
		if (self::$started) self::checkHeaders();

		ini_set('session.gc_maxlifetime', $seconds + 60 * 60);
		session_set_cookie_params($seconds);
		self::regenerateId();
	}



	/**
	 * Sets the session cookie parameters.
	 * @return void
	 */
	public static function setCookieParams($path, $domain = NULL, $secure = NULL)
	{
		if (self::$resetNeeded) self::reset();
		if (self::$started) self::checkHeaders();

		$params = session_get_cookie_params();
		session_set_cookie_params(
			$params['lifetime'],
			$path === NULL ? $params['path'] : $path,
			$domain === NULL ? $params['domain'] : $domain,
			$secure === NULL ? $params['secure'] : $secure,
			TRUE
		);
	}



	/**
	 * Sets path of the directory used to save session data.
	 * @return string
	 */
	public static function setSavePath($path)
	{
		if (self::$resetNeeded) self::reset();
		return session_save_path($path);
	}



	private static function checkHeaders()
	{
		if (headers_sent($file, $line)) {
			throw new SessionException("Headers already sent (output started at $file:$line).");
		}
	}

}




/**
 * Session Exception.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Web
 */
class SessionException extends /*::*/Exception
{
}