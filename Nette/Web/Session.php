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
 * @package    Nette\Web
 */

/*namespace Nette\Web;*/



require_once dirname(__FILE__) . '/../Object.php';



/**
 * Provides access to session namespaces as well as session settings and management methods.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Web
 */
class Session extends /*Nette\*/Object
{
	/** Default file lifetime is 3 hours */
	const DEFAULT_FILE_LIFETIME = 10800;

	/** @var callback  Validation key generator */
	public $verificationKeyGenerator;

	/** @var bool  is required session ID regeneration? */
	private $regenerationNeeded;

	/** @var bool  has been session started? */
	private static $started;

	/** @var array default configuration */
	private $options = array(
		// security
		'referer_check' => '',    // must be disabled because PHP implementation is invalid
		'use_cookies' => 1,       // must be enabled to prevent Session Hijacking and Fixation
		'use_only_cookies' => 1,  // must be enabled to prevent Session Fixation
		'use_trans_sid' => 0,     // must be disabled to prevent Session Hijacking and Fixation

		// cookies
		'cookie_lifetime' => 0,   // until the browser is closed
		'cookie_path' => '/',     // cookie is available within the entire domain
		'cookie_domain' => '',    // cookie is available on current subdomain only
		'cookie_secure' => FALSE, // cookie is available on HTTP & HTTPS
		'cookie_httponly' => TRUE,// must be enabled to prevent Session Fixation

		// other
		'gc_maxlifetime' => self::DEFAULT_FILE_LIFETIME,// 3 hours
		'cache_limiter' => NULL,  // (default "nocache", special value "\0")
		'cache_expire' => NULL,   // (default "180")
		'hash_function' => NULL,  // (default "0", means MD5)
		'hash_bits_per_character' => NULL, // (default "4")
	);



	public function __construct()
	{
		$this->verificationKeyGenerator = array($this, 'generateVerificationKey');
	}



	/**
	 * Starts and initializes session data.
	 * @throws \InvalidStateException
	 * @return void
	 */
	public function start()
	{
		if (self::$started) {
			throw new /*\*/InvalidStateException('Session has already been started.');

		} elseif (self::$started === NULL && defined('SID')) {
			throw new /*\*/InvalidStateException('A session had already been started by session.auto-start or session_start().');
		}


		// additional protection against Session Hijacking & Fixation
		if ($this->verificationKeyGenerator) {
			/**/fixCallback($this->verificationKeyGenerator);/**/
			if (!is_callable($this->verificationKeyGenerator)) {
				$able = is_callable($this->verificationKeyGenerator, TRUE, $textual);
				throw new /*\*/InvalidStateException("Verification key generator '$textual' is not " . ($able ? 'callable.' : 'valid PHP callback.'));
			}
		}


		// start session
		try {
			$this->configure($this->options);
		} catch (/*\*/NotSupportedException $e) {
			// ignore?
		}

		/*Nette\*/Tools::tryError();
		session_start();
		if (/*Nette\*/Tools::catchError($msg)) {
			@session_write_close(); // this is needed
			throw new /*\*/InvalidStateException($msg);
		}

		self::$started = TRUE;
		if ($this->regenerationNeeded) {
			session_regenerate_id(TRUE);
			$this->regenerationNeeded = FALSE;
		}

		/* structure:
			nette: __NT
			data:  __NS->namespace->variable = data
			meta:  __NM->namespace->EXP->variable = timestamp
		*/

		// initialize structures
		$verKey = $this->verificationKeyGenerator ? (string) call_user_func($this->verificationKeyGenerator) : NULL;
		if (!isset($_SESSION['__NT']['V'])) { // new session
			$_SESSION['__NT'] = array();
			$_SESSION['__NT']['C'] = 0;
			$_SESSION['__NT']['V'] = $verKey;

		} else {
			$saved = & $_SESSION['__NT']['V'];
			if ($verKey == NULL || $verKey === $saved) { // verified
				$_SESSION['__NT']['C']++;

			} else { // session attack?
				session_regenerate_id(TRUE);
				$_SESSION = array();
				$_SESSION['__NT']['C'] = 0;
				$_SESSION['__NT']['V'] = $verKey;
			}
		}

		// browser closing detection
		$browserKey = $this->getHttpRequest()->getCookie('nette-browser');
		if (!$browserKey) {
			$browserKey = (string) lcg_value();
		}
		$browserClosed = !isset($_SESSION['__NT']['B']) || $_SESSION['__NT']['B'] !== $browserKey;
		$_SESSION['__NT']['B'] = $browserKey;

		// resend cookie
		$this->sendCookie();

		// process meta metadata
		if (isset($_SESSION['__NM'])) {
			$now = time();

			// expire namespace variables
			foreach ($_SESSION['__NM'] as $namespace => $metadata) {
				if (isset($metadata['EXP'])) {
					foreach ($metadata['EXP'] as $variable => $value) {
						if (!is_array($value)) $value = array($value, !$value); // back compatibility

						list($time, $whenBrowserIsClosed) = $value;
						if (($whenBrowserIsClosed && $browserClosed) || ($time && $now > $time)) {
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
		return (bool) self::$started;
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
	 * @return void
	 */
	public function destroy()
	{
		if (!self::$started) {
			throw new /*\*/InvalidStateException('Session is not started.');
		}

		session_destroy();
		$_SESSION = NULL;
		self::$started = FALSE;
		if (!$this->getHttpResponse()->isSent()) {
			$params = session_get_cookie_params();
			$this->getHttpResponse()->deleteCookie(session_name(), $params['path'], $params['domain'], $params['secure']);
		}
	}



	/**
	 * Does session exists for the current request?
	 * @return bool
	 */
	public function exists()
	{
		return self::$started || $this->getHttpRequest()->getCookie(session_name()) !== NULL;
	}



	/**
	 * Regenerates the session ID.
	 * @throws \InvalidStateException
	 * @return void
	 */
	public function regenerateId()
	{
		if (self::$started) {
			if (headers_sent($file, $line)) {
				throw new /*\*/InvalidStateException("Cannot regenerate session ID after HTTP headers have been sent" . ($file ? " (output started at $file:$line)." : "."));
			}
			session_regenerate_id(TRUE);

		} else {
			$this->regenerationNeeded = TRUE;
		}
	}



	/**
	 * Returns the current session ID. Don't make dependencies, can be changed for each request.
	 * @return string
	 */
	public function getId()
	{
		return session_id();
	}



	/**
	 * Sets the session name to a specified one.
	 * @param  string
	 * @return Session  provides a fluent interface
	 */
	public function setName($name)
	{
		if (!is_string($name) || !preg_match('#[^0-9.][^.]*$#A', $name)) {
			throw new /*\*/InvalidArgumentException('Session name must be a string and cannot contain dot.');
		}

		session_name($name);
		return $this->setOptions(array(
			'name' => $name,
		));
	}



	/**
	 * Gets the session name.
	 * @return string
	 */
	public function getName()
	{
		return session_name();
	}



	/**
	 * Generates key as protection against Session Hijacking & Fixation.
	 * @return string
	 */
	public function generateVerificationKey()
	{
		$httpRequest = $this->getHttpRequest();
		$key[] = $httpRequest->getHeader('Accept-Charset');
		$key[] = $httpRequest->getHeader('Accept-Encoding');
		$key[] = $httpRequest->getHeader('Accept-Language');
		$key[] = $httpRequest->getHeader('User-Agent');
		if (strpos($key[3], 'MSIE 8.0')) { // IE 8 AJAX bug
			$key[2] = substr($key[2], 0, 2);
		}
		return md5(implode("\0", $key));
	}



	/********************* namespaces management ****************d*g**/



	/**
	 * Returns specified session namespace.
	 * @param  string
	 * @param  string
	 * @return SessionNamespace
	 * @throws \InvalidArgumentException
	 */
	public function getNamespace($namespace, $class = /*Nette\Web\*/'SessionNamespace')
	{
		if (!is_string($namespace) || $namespace === '') {
			throw new /*\*/InvalidArgumentException('Session namespace must be a non-empty string.');
		}

		if (!self::$started) {
			$this->start();
		}

		return new $class($_SESSION['__NS'][$namespace], $_SESSION['__NM'][$namespace]);
	}



	/**
	 * Checks if a session namespace exist and is not empty.
	 * @param  string
	 * @return bool
	 */
	public function hasNamespace($namespace)
	{
		if ($this->exists() && !self::$started) {
			$this->start();
		}

		return !empty($_SESSION['__NS'][$namespace]);
	}



	/**
	 * Iteration over all namespaces.
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		if ($this->exists() && !self::$started) {
			$this->start();
		}

		if (isset($_SESSION['__NS'])) {
			return new /*\*/ArrayIterator(array_keys($_SESSION['__NS']));

		} else {
			return new /*\*/ArrayIterator;
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

		if (empty($_SESSION)) {
			//$this->destroy(); only when shutting down
		}
	}



	/********************* configuration ****************d*g**/



	/**
	 * Sets session options.
	 * @param  array
	 * @return Session  provides a fluent interface
	 * @throws \NotSupportedException
	 * @throws \InvalidStateException
	 */
	public function setOptions(array $options)
	{
		if (self::$started) {
			$this->configure($options);
		}
		$this->options = $options + $this->options;
		return $this;
	}



	/**
	 * Returns all session options.
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}



	/**
	 * Configurates session environment.
	 * @param  array
	 * @return void
	 */
	private function configure(array $config)
	{
		$special = array('cache_expire' => 1, 'cache_limiter' => 1, 'save_path' => 1, 'name' => 1);

		foreach ($config as $key => $value) {
			if (!strncmp($key, 'session.', 8)) { // back compatibility
				$key = substr($key, 8);
			}

			if ($value === NULL) {
				continue;

			} elseif (isset($special[$key])) {
				if (self::$started) {
					throw new /*\*/InvalidStateException("Unable to set '$key' when session has been started.");
				}
				$key = "session_$key";
				$key($value);

			} elseif (strncmp($key, 'cookie_', 7) === 0) {
				if (!isset($cookie)) {
					$cookie = session_get_cookie_params();
				}
				$cookie[substr($key, 7)] = $value;

			} elseif (!function_exists('ini_set')) {
				if (ini_get($key) != $value) { // intentionally ==
					throw new /*\*/NotSupportedException('Required function ini_set() is disabled.');
				}

			} else {
				if (self::$started) {
					throw new /*\*/InvalidStateException("Unable to set '$key' when session has been started.");
				}
				ini_set("session.$key", $value);
			}
		}

		if (isset($cookie)) {
			session_set_cookie_params($cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
			if (self::$started) {
				$this->sendCookie();
			}
		}
	}



	/**
	 * Sets the amount of time allowed between requests before the session will be terminated.
	 * @param  mixed  number of seconds, value 0 means "until the browser is closed"
	 * @return Session  provides a fluent interface
	 */
	public function setExpiration($seconds)
	{
		if (is_string($seconds) && !is_numeric($seconds)) {
			$seconds = strtotime($seconds);
		}

		if ($seconds <= 0) {
			return $this->setOptions(array(
				'gc_maxlifetime' => self::DEFAULT_FILE_LIFETIME,
				'cookie_lifetime' => 0,
			));

		} else {
			if ($seconds > /*Nette\*/Tools::YEAR) {
				$seconds -= time();
			}
			return $this->setOptions(array(
				'gc_maxlifetime' => $seconds,
				'cookie_lifetime' => $seconds,
			));
		}
	}



	/**
	 * Sets the session cookie parameters.
	 * @param  string  path
	 * @param  string  domain
	 * @param  bool    secure
	 * @return Session  provides a fluent interface
	 */
	public function setCookieParams($path, $domain = NULL, $secure = NULL)
	{
		return $this->setOptions(array(
			'cookie_path' => $path,
			'cookie_domain' => $domain,
			'cookie_secure' => $secure
		));
	}



	/**
	 * Returns the session cookie parameters.
	 * @return array  containing items: lifetime, path, domain, secure, httponly
	 */
	public function getCookieParams()
	{
		return session_get_cookie_params();
	}



	/**
	 * Sets path of the directory used to save session data.
	 * @return Session  provides a fluent interface
	 */
	public function setSavePath($path)
	{
		return $this->setOptions(array(
			'save_path' => $path,
		));
	}



	/**
	 * Sends the session cookies.
	 * @return void
	 */
	private function sendCookie()
	{
		$cookie = $this->getCookieParams();
		$this->getHttpResponse()->setCookie(session_name(), session_id(), $cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
		$this->getHttpResponse()->setCookie('nette-browser', $_SESSION['__NT']['B'], HttpResponse::BROWSER, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
	}



	/********************* backend ****************d*g**/



	/**
	 * @return Nette\Web\IHttpRequest
	 */
	protected function getHttpRequest()
	{
		return /*Nette\*/Environment::getHttpRequest();
	}



	/**
	 * @return Nette\Web\IHttpResponse
	 */
	protected function getHttpResponse()
	{
		return /*Nette\*/Environment::getHttpResponse();
	}

}
