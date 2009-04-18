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
 * @version    $Id$
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
	private static $defaultConfig = array(
		// security
		'session.referer_check' => '',    // must be disabled because PHP implementation is invalid
		'session.use_cookies' => 1,       // must be enabled to prevent Session Hijacking and Fixation
		'session.use_only_cookies' => 1,  // must be enabled to prevent Session Fixation
		'session.use_trans_sid' => 0,     // must be disabled to prevent Session Hijacking and Fixation

		// cookies
		'session.cookie_lifetime' => 0,   // until the browser is closed
		'session.cookie_path' => '/',    // cookie is available within the entire domain
		'session.cookie_domain' => '',    // cookie is available on current subdomain only
		'session.cookie_secure' => FALSE, // cookie is available on HTTP & HTTPS
		'session.cookie_httponly' => TRUE,// must be enabled to prevent Session Fixation

		// other
		'session.gc_maxlifetime' => self::DEFAULT_FILE_LIFETIME,// 3 hours
		'session.cache_limiter' => NULL,  // (default "nocache", special value "\0")
		'session.cache_expire' => NULL,   // (default "180")
		'session.hash_function' => NULL,  // (default "0", means MD5)
		'session.hash_bits_per_character' => NULL, // (default "4")
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
		$this->configure(self::$defaultConfig, FALSE);

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
		$verKey = $this->verificationKeyGenerator ? (string) call_user_func($this->verificationKeyGenerator) : '';
		if (!isset($_SESSION['__NT']['V'])) { // new session
			$_SESSION['__NT'] = array();
			$_SESSION['__NT']['C'] = 0;
			$_SESSION['__NT']['V'] = $verKey;

		} else {
			$saved = & $_SESSION['__NT']['V'];
			if ($saved === $verKey) { // verified
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
					foreach ($metadata['EXP'] as $variable => $time) {
						if ((!$time && $browserClosed) || ($time && $now > $time)) {
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
		if (!headers_sent()) {
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
			$_SESSION['__NT']['V'] = $this->verificationKeyGenerator ? (string) call_user_func($this->verificationKeyGenerator) : '';
			session_regenerate_id(TRUE);

		} else {
			$this->regenerationNeeded = TRUE;
		}
	}



	/**
	 * Sets the session ID to a specified one.
	 * @deprecated
	 */
	public function setId($id)
	{
		throw new /*\*/DeprecatedException('Method '.__METHOD__.'() is deprecated.');
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
	 * @return void
	 */
	public function setName($name)
	{
		if (!is_string($name) || !preg_match('#[^0-9.][^.]*$#A', $name)) {
			throw new /*\*/InvalidArgumentException('Session name must be a string and cannot contain dot.');
		}

		$this->configure(array(
			'session.name' => $name,
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
		$list = array('Accept-Charset', 'Accept-Encoding', 'Accept-Language', 'User-Agent');
		$key = array();
		$httpRequest = $this->getHttpRequest();
		foreach ($list as $header) {
			$key[] = $httpRequest->getHeader($header);
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
	 * Configurates session environment.
	 * @param  array
	 * @param  bool   throw exception?
	 * @return void
	 * @throws \NotSupportedException
	 * @throws \InvalidStateException
	 */
	public function configure(array $config, $throwException = TRUE)
	{
		$special = array('session.cache_expire' => 1, 'session.cache_limiter' => 1,
			'session.save_path' => 1, 'session.name' => 1);

		foreach ($config as $key => $value) {
			unset(self::$defaultConfig[$key]); // prevents overwriting

			if ($value === NULL) {
				continue;

			} elseif (isset($special[$key])) {
				if (self::$started) {
					throw new /*\*/InvalidStateException('Session has already been started.');
				}
				$key = strtr($key, '.', '_');
				$key($value);

			} elseif (strncmp($key, 'session.cookie_', 15) === 0) {
				if (!isset($cookie)) {
					$cookie = session_get_cookie_params();
				}
				$cookie[substr($key, 15)] = $value;

			} elseif (!function_exists('ini_set')) {
				if ($throwException && ini_get($key) != $value) { // intentionally ==
					throw new /*\*/NotSupportedException('Required function ini_set() is disabled.');
				}

			} else {
				if (self::$started) {
					throw new /*\*/InvalidStateException('Session has already been started.');
				}
				ini_set($key, $value);
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
	 * @param  int  number of seconds, value 0 means "until the browser is closed"
	 * @return void
	 */
	public function setExpiration($seconds)
	{
		if ($seconds <= 0) {
			$this->configure(array(
				'session.gc_maxlifetime' => self::DEFAULT_FILE_LIFETIME,
				'session.cookie_lifetime' => 0,
			));

		} else {
			if ($seconds > /*Nette\*/Tools::YEAR) {
				$seconds -= time();
			}
			$this->configure(array(
				'session.gc_maxlifetime' => $seconds,
				'session.cookie_lifetime' => $seconds,
			));
		}
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
	 * Returns the session cookie parameters.
	 * @return array  containing items: lifetime, path, domain, secure, httponly
	 */
	public function getCookieParams()
	{
		return session_get_cookie_params();
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
