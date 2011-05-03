<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Http;

use Nette;



/**
 * Provides access to session namespaces as well as session settings and management methods.
 *
 * @author     David Grudl
 */
class Session extends Nette\Object
{
	/** Default file lifetime is 3 hours */
	const DEFAULT_FILE_LIFETIME = 10800;

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
		'cookie_httponly' => TRUE,// must be enabled to prevent Session Hijacking

		// other
		'gc_maxlifetime' => self::DEFAULT_FILE_LIFETIME,// 3 hours
		'cache_limiter' => NULL,  // (default "nocache", special value "\0")
		'cache_expire' => NULL,   // (default "180")
		'hash_function' => NULL,  // (default "0", means MD5)
		'hash_bits_per_character' => NULL, // (default "4")
	);

	/** @var IRequest */
	private $request;

	/** @var IResponse */
	private $response;



	public function __construct(IRequest $request, IResponse $response)
	{
		$this->request = $request;
		$this->response = $response;
	}



	/**
	 * Starts and initializes session data.
	 * @throws Nette\InvalidStateException
	 * @return void
	 */
	public function start()
	{
		if (self::$started) {
			return;

		} elseif (self::$started === NULL && defined('SID')) {
			throw new Nette\InvalidStateException('A session had already been started by session.auto-start or session_start().');
		}

		$this->configure($this->options);

		Nette\Diagnostics\Debugger::tryError();
		session_start();
		if (Nette\Diagnostics\Debugger::catchError($e)) {
			@session_write_close(); // this is needed
			throw new Nette\InvalidStateException('session_start(): ' . $e->getMessage(), 0, $e);
		}

		self::$started = TRUE;
		if ($this->regenerationNeeded) {
			session_regenerate_id(TRUE);
			$this->regenerationNeeded = FALSE;
		}

		/* structure:
			__NF: Counter, BrowserKey, Data, Meta
				DATA: namespace->variable = data
				META: namespace->variable = Timestamp, Browser, Version
		*/

		unset($_SESSION['__NT'], $_SESSION['__NS'], $_SESSION['__NM']); // old unused structures

		// initialize structures
		$nf = & $_SESSION['__NF'];
		if (empty($nf)) { // new session
			$nf = array('C' => 0);
		} else {
			$nf['C']++;
		}

		// browser closing detection
		$browserKey = $this->request->getCookie('nette-browser');
		if (!$browserKey) {
			$browserKey = Nette\Utils\Strings::random();
		}
		$browserClosed = !isset($nf['B']) || $nf['B'] !== $browserKey;
		$nf['B'] = $browserKey;

		// resend cookie
		$this->sendCookie();

		// process meta metadata
		if (isset($nf['META'])) {
			$now = time();
			// expire namespace variables
			foreach ($nf['META'] as $namespace => $metadata) {
				if (is_array($metadata)) {
					foreach ($metadata as $variable => $value) {
						if ((!empty($value['B']) && $browserClosed) || (!empty($value['T']) && $now > $value['T']) // whenBrowserIsClosed || Time
							|| ($variable !== '' && is_object($nf['DATA'][$namespace][$variable]) && (isset($value['V']) ? $value['V'] : NULL) // Version
								!== Nette\Reflection\ClassType::from($nf['DATA'][$namespace][$variable])->getAnnotation('serializationVersion'))
						) {
							if ($variable === '') { // expire whole namespace
								unset($nf['META'][$namespace], $nf['DATA'][$namespace]);
								continue 2;
							}
							unset($nf['META'][$namespace][$variable], $nf['DATA'][$namespace][$variable]);
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
			$this->clean();
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
			throw new Nette\InvalidStateException('Session is not started.');
		}

		session_destroy();
		$_SESSION = NULL;
		self::$started = FALSE;
		if (!$this->response->isSent()) {
			$params = session_get_cookie_params();
			$this->response->deleteCookie(session_name(), $params['path'], $params['domain'], $params['secure']);
		}
	}



	/**
	 * Does session exists for the current request?
	 * @return bool
	 */
	public function exists()
	{
		return self::$started || $this->request->getCookie(session_name()) !== NULL;
	}



	/**
	 * Regenerates the session ID.
	 * @throws Nette\InvalidStateException
	 * @return void
	 */
	public function regenerateId()
	{
		if (self::$started) {
			if (headers_sent($file, $line)) {
				throw new Nette\InvalidStateException("Cannot regenerate session ID after HTTP headers have been sent" . ($file ? " (output started at $file:$line)." : "."));
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
			throw new Nette\InvalidArgumentException('Session name must be a string and cannot contain dot.');
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



	/********************* namespaces management ****************d*g**/



	/**
	 * Returns specified session namespace.
	 * @param  string
	 * @param  string
	 * @return SessionNamespace
	 * @throws Nette\InvalidArgumentException
	 */
	public function getNamespace($namespace, $class = 'Nette\Http\SessionNamespace')
	{
		if (!is_string($namespace) || $namespace === '') {
			throw new Nette\InvalidArgumentException('Session namespace must be a non-empty string.');
		}

		if (!self::$started) {
			$this->start();
		}

		return new $class($_SESSION['__NF']['DATA'][$namespace], $_SESSION['__NF']['META'][$namespace]);
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

		return !empty($_SESSION['__NF']['DATA'][$namespace]);
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

		if (isset($_SESSION['__NF']['DATA'])) {
			return new \ArrayIterator(array_keys($_SESSION['__NF']['DATA']));

		} else {
			return new \ArrayIterator;
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

		$nf = & $_SESSION['__NF'];
		if (isset($nf['META']) && is_array($nf['META'])) {
			foreach ($nf['META'] as $name => $foo) {
				if (empty($nf['META'][$name])) {
					unset($nf['META'][$name]);
				}
			}
		}

		if (empty($nf['META'])) {
			unset($nf['META']);
		}

		if (empty($nf['DATA'])) {
			unset($nf['DATA']);
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
	 * @throws Nette\NotSupportedException
	 * @throws Nette\InvalidStateException
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
					throw new Nette\InvalidStateException("Unable to set '$key' when session has been started.");
				}
				$key = "session_$key";
				$key($value);

			} elseif (strncmp($key, 'cookie_', 7) === 0) {
				if (!isset($cookie)) {
					$cookie = session_get_cookie_params();
				}
				$cookie[substr($key, 7)] = $value;

			} elseif (!function_exists('ini_set')) {
				if (ini_get($key) != $value && !Nette\Framework::$iAmUsingBadHost) { // intentionally ==
					throw new Nette\NotSupportedException('Required function ini_set() is disabled.');
				}

			} else {
				if (self::$started) {
					throw new Nette\InvalidStateException("Unable to set '$key' when session has been started.");
				}
				ini_set("session.$key", $value);
			}
		}

		if (isset($cookie)) {
			session_set_cookie_params(
				$cookie['lifetime'], $cookie['path'], $cookie['domain'],
				$cookie['secure'], $cookie['httponly']
			);
			if (self::$started) {
				$this->sendCookie();
			}
		}
	}



	/**
	 * Sets the amount of time allowed between requests before the session will be terminated.
	 * @param  string|int|DateTime  time, value 0 means "until the browser is closed"
	 * @return Session  provides a fluent interface
	 */
	public function setExpiration($time)
	{
		if (empty($time)) {
			return $this->setOptions(array(
				'gc_maxlifetime' => self::DEFAULT_FILE_LIFETIME,
				'cookie_lifetime' => 0,
			));

		} else {
			$time = Nette\DateTime::from($time)->format('U') - time();
			return $this->setOptions(array(
				'gc_maxlifetime' => $time,
				'cookie_lifetime' => $time,
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
	 * Sets user session storage.
	 * @return Session  provides a fluent interface
	 */
	public function setStorage(ISessionStorage $storage)
	{
		if (self::$started) {
			throw new Nette\InvalidStateException("Unable to set storage when session has been started.");
		}
		session_set_save_handler(
			array($storage, 'open'), array($storage, 'close'), array($storage, 'read'),
			array($storage, 'write'), array($storage, 'remove'), array($storage, 'clean')
		);
	}



	/**
	 * Sends the session cookies.
	 * @return void
	 */
	private function sendCookie()
	{
		$cookie = $this->getCookieParams();
		$this->response->setCookie(
			session_name(), session_id(),
			$cookie['lifetime'] ? $cookie['lifetime'] + time() : 0,
			$cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']

		)->setCookie(
			'nette-browser', $_SESSION['__NF']['B'],
			Response::BROWSER, $cookie['path'], $cookie['domain']
		);
	}

}
