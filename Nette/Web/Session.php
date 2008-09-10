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



require_once dirname(__FILE__) . '/../Object.php';



/**
 * Provides access to session namespaces as well as session settings and management methods.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Web
 */
class Session extends /*Nette::*/Object
{
	/** @var callback  Validation key generator */
	public $verificationKeyGenerator;

	/** @var bool  is required session id regeneration? */
	private $regenerationNeeded;

	/** @var bool  has been session started? */
	private static $started = FALSE;

	/** @var array of SessionNamespace  registry of singleton instances */
	private static $instances = array();

	/** @var array */
	private static $configuration = array(
		// security
		'session.referer_check' => '',    // default "" (must be disabled because PHP implementation is invalid)
		'session.use_cookies' => 1,       // default "1" (must be enabled to prevent Session Hijacking and Fixation)
		'session.use_only_cookies' => 1,  // default "1" (must be enabled to prevent Session Fixation)
		'session.use_trans_sid' => 0,     // default "0" (must be disabled to prevent Session Hijacking and Fixation)

		// cookies
		'session.cookie_path ' => '/',    // default "/"
		'session.cookie_domain' => '',    // default ""
		'session.cookie_secure' => FALSE, // default ""
		'session.cookie_httponly' => TRUE,// default "" (must be enabled to prevent Session Fixation)

		// misc
		'session_cache_limiter' => 'none',// default "nocache" (do not affect caching)
		'session_cache_expire' => NULL,   // default "180"
		'session.hash_function' => NULL,  // default "0" (MD5)
		'session.hash_bits_per_character' => NULL, // default "4"
	);



	public function __construct()
	{
		$this->verificationKeyGenerator = array($this, 'generateVerificationKey');
	}



	/**
	 * Starts and initializes session data.
	 * @throws ::InvalidStateException
	 * @return void
	 */
	public function start()
	{
		if (self::$started) {
			throw new /*::*/InvalidStateException('Session has already been started.');

		} elseif (defined('SID')) {
			throw new /*::*/InvalidStateException('A session had already been started by session.auto-start or session_start().');
		}

		$this->configure(self::$configuration);

		/*Nette::*/Tools::tryError();
		session_start();
		if (/*Nette::*/Tools::catchError($msg)) {
			@session_write_close(); // this is needed
			throw new /*::*/InvalidStateException($msg);
		}

		self::$started = TRUE;
		if ($this->regenerationNeeded) {
			session_regenerate_id(TRUE);
			$this->regenerationNeeded = FALSE;
		}


		/*
		nette: __NT
		data:  __NS->namespace->variable = data
		meta:  __NM->namespace->EXP->variable = timestamp
		*/

		// additional protection against Session Hijacking & Fixation
		$key = $this->verificationKeyGenerator ? (string) call_user_func($this->verificationKeyGenerator) : '';

		if (!isset($_SESSION['__NT']['V'])) { // new session
			$_SESSION['__NT'] = array();
			$_SESSION['__NT']['C'] = 0;
			$_SESSION['__NT']['V'] = $key;

		} else {
			$saved = & $_SESSION['__NT']['V'];
			if ($saved === $key) { // verified
				$_SESSION['__NT']['C']++;

			} else { // session attack?
				session_regenerate_id(TRUE);
				$_SESSION = array();
				$_SESSION['__NT']['C'] = 0;
				$_SESSION['__NT']['V'] = $key;
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

		register_shutdown_function(array($this, 'clean'));
	}



	/**
	 * Has been session started?
	 * @return bool
	 */
	public function isStarted()
	{
		return self::$started;
	}



	/**
	 * Ends the current session and store session data.
	 * @return void
	 */
	public function close()
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
	public function destroy($removeCookie = TRUE)
	{
		if (!self::$started) {
			throw new /*::*/InvalidStateException('Session is not started.');
		}

		session_destroy();
		$_SESSION = NULL;
		self::$started = FALSE;

		if ($removeCookie) {
			// TODO: Environment::getHttpResponse()->headersSent, deleteCookie
			if (headers_sent($file, $line)) {
				throw new /*::*/InvalidStateException("Headers already sent (output started at $file:$line).");
			}
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
	public function exists()
	{
		// TODO: return Environment::getHttpRequest()->getCookie(session_name()) !== NULL;
		return isset($_COOKIE[session_name()]);
	}



	/**
	 * Regenerates the session id.
	 * @throws ::InvalidStateException
	 * @return void
	 */
	public function regenerateId()
	{
		if (self::$started) {
			// TODO: Environment::getHttpResponse()->headersSent
			if (headers_sent($file, $line)) {
				throw new /*::*/InvalidStateException("Headers already sent (output started at $file:$line).");
			}
			$_SESSION['__NT']['V'] = $this->verificationKeyGenerator ? (string) call_user_func($this->verificationKeyGenerator) : '';
			session_regenerate_id(TRUE);

		} else {
			$this->regenerationNeeded = TRUE;
		}
	}



	/**
	 * Sets the session id to a user specified one.
	 * @throws ::InvalidStateException
	 * @param  string $id
	 * @return void
	 */
	public function setId($id)
	{
		if (defined('SID')) {
			throw new /*::*/InvalidStateException('A session had already been started - the session id must be set first.');
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
	public function getId()
	{
		return session_id();
	}



	/**
	 * Generates key as protection against Session Hijacking & Fixation.
	 * @return string
	 */
	public function generateVerificationKey()
	{
		//return; // debug
		$list = array(
			'HTTP_ACCEPT_CHARSET', 'HTTP_ACCEPT_ENCODING',
			'HTTP_ACCEPT_LANGUAGE', 'HTTP_USER_AGENT',
		);

		$key = array();
		foreach ($list as $item) {
			// TODO: $key[] = $httpRequest->getHeader($header)
			if (isset($_SERVER[$item])) $key[] = $_SERVER[$item];
		}
		return md5(implode("\0", $key));
	}



	/********************* namespaces management ****************d*g**/



	/**
	 * Returns instance of session namespace.
	 * @param  string
	 * @param  string
	 * @return SessionNamespace
	 * @throws ::InvalidArgumentException
	 */
	public function getNamespace($namespace, $class = /*Nette::Web::*/'SessionNamespace')
	{
		if (!is_string($namespace) || $namespace === '') {
			throw new /*::*/InvalidArgumentException('Session namespace must be a non-empty string.');
		}

		if (!self::$started) {
			$this->start();
		}

		if (!isset(self::$instances[$namespace])) {
			self::$instances[$namespace] = new $class($_SESSION['__NS'][$namespace], $_SESSION['__NM'][$namespace]);
		}

		return self::$instances[$namespace];
	}



	/**
	 * Checks if a namespace exists.
	 * @param  string
	 * @return bool
	 */
	public function hasNamespace($namespace)
	{
		if (!self::$started) {
			$this->start();
		}

		return isset($_SESSION['__NS'][$namespace]);
	}



	/**
	 * Iteration over all namespaces.
	 * @return ::ArrayIterator
	 */
	public function getIterator()
	{
		if (!self::$started) {
			$this->start();
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
	public function clean()
	{
		if (!self::$started || empty($_SESSION)) {
			return;
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
	 * Configurates session environment.
	 * @param  array
	 * @return void
	 */
	public function configure(array $config)
	{
		// TODO: Environment::getHttpResponse()->headersSent
		if (headers_sent($file, $line)) {
			throw new /*::*/InvalidStateException("Headers already sent (output started at $file:$line).");
		}

		$special = array('session.cache_expire' => 1, 'session.cache_limiter' => 1, 'session.save_path' => 1, 'session.name' => 1);
		$hasIniSet = function_exists('ini_set');

		foreach ($config as $key => $value) {
			if ($value === NULL) {
				continue;

			} elseif (isset($special[$key])) {
				$key = strtr($key, '.', '_');
				$key($value);

			} elseif (strncmp($key, 'session.cookie_', 15) === 0) {
				if (!isset($cookie)) {
					$cookie = session_get_cookie_params();
					//foreach ($cookie as $k => $v) self::$configuration['session.cookie_' . $k] = $v;
				}
				$cookie[substr($key, 15)] = $value;

			} elseif (!$hasIniSet) {
				// TODO: what to do?

			} else {
				ini_set($key, $value);
			}

			self::$configuration[$key] = $value;
		}

		if (isset($cookie)) {
			session_set_cookie_params($cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
		}
	}



	/**
	 * Sets the amount of time in seconds allowed between
	 * requests before the session will be terminated.
	 * @param  int  number of seconds
	 * @return void
	 */
	public function setTimeout($seconds)
	{
		$this->configure(array(
			'session.gc_maxlifetime' => $seconds + 60 * 60,
			'session.cookie_lifetime' => $seconds,
		));
		$this->regenerateId();
	}



	/**
	 * Sets the session cookie parameters.
	 * @param  string  path
	 * @param  string  domain
	 * @param  bool    secure
	 * @return void
	 */
	public function setCookieParams($path, $domain = NULL, $secure = NULL)
	{
		$this->configure(array(
			'session.cookie_path' => $path,
			'session.cookie_domain' => $domain,
			'session.cookie_secure' => $secure
		));
	}



	/**
	 * Sets path of the directory used to save session data.
	 * @return void
	 */
	public function setSavePath($path)
	{
		$this->configure(array(
			'session.save_path' => $path,
		));
	}

}
