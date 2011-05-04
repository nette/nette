<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\DI;

use Nette,
	Nette\ArrayHash,
	Nette\Environment;



/**
 * Nette\Environment helper.
 *
 * @author     David Grudl
 */
class Configurator extends Nette\Object
{
	/** @var string */
	public $defaultConfigFile = '%appDir%/config.neon';

	/** @var array */
	public $defaultServices = array(
		'Nette\\Application\\Application' => array(__CLASS__, 'createApplication'),
		'Nette\\Web\\HttpContext' => array(__CLASS__, 'createHttpContext'),
		'Nette\\Web\\IHttpRequest' => array(__CLASS__, 'createHttpRequest'),
		'Nette\\Web\\IHttpResponse' => 'Nette\Http\Response',
		'Nette\\Web\\IUser' => array(__CLASS__, 'createHttpUser'),
		'Nette\\Caching\\ICacheStorage' => array(__CLASS__, 'createCacheStorage'),
		'Nette\\Caching\\ICacheJournal' => array(__CLASS__, 'createCacheJournal'),
		'Nette\\Mail\\IMailer' => array(__CLASS__, 'createMailer'),
		'Nette\\Web\\Session' => array(__CLASS__, 'createHttpSession'),
		'Nette\\Loaders\\RobotLoader' => array(__CLASS__, 'createRobotLoader'),
		'templateCacheStorage' => array(__CLASS__, 'createTemplateCacheStorage'),
	);



	/**
	 * Detect environment mode.
	 * @param  string mode name
	 * @return bool
	 */
	public function detect($name)
	{
		switch ($name) {
		case 'environment':
			// environment name autodetection
			if ($this->detect('console')) {
				return Environment::CONSOLE;

			} else {
				return Environment::getMode('production') ? Environment::PRODUCTION : Environment::DEVELOPMENT;
			}

		case 'production':
			// detects production mode by server IP address
			if (PHP_SAPI === 'cli') {
				return FALSE;

			} elseif (isset($_SERVER['SERVER_ADDR']) || isset($_SERVER['LOCAL_ADDR'])) {
				$addrs = array();
				if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { // proxy server detected
					$addrs = preg_split('#,\s*#', $_SERVER['HTTP_X_FORWARDED_FOR']);
				}
				if (isset($_SERVER['REMOTE_ADDR'])) {
					$addrs[] = $_SERVER['REMOTE_ADDR'];
				}
				$addrs[] = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
				foreach ($addrs as $addr) {
					$oct = explode('.', $addr);
					// 10.0.0.0/8   Private network
					// 127.0.0.0/8  Loopback
					// 169.254.0.0/16 & ::1  Link-Local
					// 172.16.0.0/12  Private network
					// 192.168.0.0/16  Private network
					if ($addr !== '::1' && (count($oct) !== 4 || ($oct[0] !== '10' && $oct[0] !== '127' && ($oct[0] !== '172' || $oct[1] < 16 || $oct[1] > 31)
						&& ($oct[0] !== '169' || $oct[1] !== '254') && ($oct[0] !== '192' || $oct[1] !== '168')))
					) {
						return TRUE;
					}
				}
				return FALSE;

			} else {
				return TRUE;
			}

		case 'console':
			return PHP_SAPI === 'cli';

		default:
			// unknown mode
			return NULL;
		}
	}



	/**
	 * Loads global configuration from file and process it.
	 * @param  string  file name
	 * @return Nette\ArrayHash
	 */
	public function loadConfig($file)
	{
		$name = Environment::getName();

		if ($file instanceof ArrayHash) {
			$config = $file;
			$file = NULL;

		} else {
			if ($file === NULL) {
				$file = $this->defaultConfigFile;
			}
			$file = Environment::expand($file);
			if (!is_file($file)) {
				$file = preg_replace('#\.neon$#', '.ini', $file); // backcompatibility
			}
			$config = Nette\Config\Config::fromFile($file, $name);
		}

		// process environment variables
		if (isset($config->variable) && $config->variable instanceof \Traversable) {
			foreach ($config->variable as $key => $value) {
				Environment::setVariable($key, $value);
			}
		}

		// expand variables
		$iterator = new \RecursiveIteratorIterator($config);
		foreach ($iterator as $key => $value) {
			$tmp = $iterator->getDepth() ? $iterator->getSubIterator($iterator->getDepth() - 1)->current() : $config;
			$tmp[$key] = Environment::expand($value);
		}

		// process services
		$runServices = array();
		$container = Environment::getContext();
		if (isset($config->service) && $config->service instanceof \Traversable) {
			foreach ($config->service as $key => $value) {
				$key = strtr($key, '-', '\\'); // limited INI chars
				if (is_string($value)) {
					$container->removeService($key);
					$container->addService($key, $value);
				} else {
					if (!empty($value->factory) || isset($this->defaultServices[$key])) {
						$factory = empty($value->factory) ? $this->defaultServices[$key] : $value->factory;
						if (!empty($value->option)) {
							$factory = function() use ($container, $factory, $value) {
								return call_user_func($factory, $container, (array) $value->option);
							};
						}
						$container->removeService($key);
						$container->addService($key, $factory);
					} else {
						throw new Nette\InvalidStateException("Factory method is not specified for service $key.");
					}
					if (!empty($value->run)) {
						$runServices[] = $key;
					}
				}
			}
		}

		// process ini settings
		if (!isset($config->php) && isset($config->set)) { // backcompatibility
			$config->php = $config->set;
			unset($config->set);
		}

		if (isset($config->php) && $config->php instanceof \Traversable) {
			if (PATH_SEPARATOR !== ';' && isset($config->php->include_path)) {
				$config->php->include_path = str_replace(';', PATH_SEPARATOR, $config->php->include_path);
			}

			foreach (clone $config->php as $key => $value) { // flatten INI dots
				if ($value instanceof \Traversable) {
					unset($config->php->$key);
					foreach ($value as $k => $v) {
						$config->php->{"$key.$k"} = $v;
					}
				}
			}

			foreach ($config->php as $key => $value) {
				$key = strtr($key, '-', '.'); // backcompatibility

				if (!is_scalar($value)) {
					throw new Nette\InvalidStateException("Configuration value for directive '$key' is not scalar.");
				}

				if ($key === 'date.timezone') { // PHP bug #47466
					date_default_timezone_set($value);
				}

				if (function_exists('ini_set')) {
					ini_set($key, $value);
				} else {
					switch ($key) {
					case 'include_path':
						set_include_path($value);
						break;
					case 'iconv.internal_encoding':
						iconv_set_encoding('internal_encoding', $value);
						break;
					case 'mbstring.internal_encoding':
						mb_internal_encoding($value);
						break;
					case 'date.timezone':
						date_default_timezone_set($value);
						break;
					case 'error_reporting':
						error_reporting($value);
						break;
					case 'ignore_user_abort':
						ignore_user_abort($value);
						break;
					case 'max_execution_time':
						set_time_limit($value);
						break;
					default:
						if (ini_get($key) != $value) { // intentionally ==
							throw new Nette\NotSupportedException('Required function ini_set() is disabled.');
						}
					}
				}
			}
		}

		// define constants
		if (isset($config->const) && $config->const instanceof \Traversable) {
			foreach ($config->const as $key => $value) {
				define($key, $value);
			}
		}

		// set modes
		if (isset($config->mode) && isset($config->mode)) {
			foreach ($config->mode as $mode => $state) {
				Environment::setMode($mode, $state);
			}
		}

		// auto-start services
		foreach ($runServices as $name) {
			$container->getService($name);
		}

		return $config;
	}



	/********************* service factories ****************d*g**/



	/**
	 * Get initial instance of context.
	 * @return IContainer
	 */
	public function createContainer()
	{
		$container = new Container;
		foreach ($this->defaultServices as $name => $service) {
			$container->addService($name, $service);
		}
		return $container;
	}



	/**
	 * @return Nette\Application\Application
	 */
	public static function createApplication(IContainer $container, array $options = NULL)
	{
		$container = clone $container;
		$container->addService('Nette\\Application\\IRouter', 'Nette\Application\Routers\RouteList');

		if (!$container->hasService('Nette\\Application\\IPresenterFactory')) {
			$container->addService('Nette\\Application\\IPresenterFactory', function() use ($container) {
				return new Nette\Application\PresenterFactory(Environment::getVariable('appDir'), $container);
			});
		}

		Nette\Application\UI\Presenter::$invalidLinkMode = Environment::isProduction()
			? Nette\Application\UI\Presenter::INVALID_LINK_SILENT
			: Nette\Application\UI\Presenter::INVALID_LINK_WARNING;

		$class = isset($options['class']) ? $options['class'] : 'Nette\Application\Application';
		$application = new $class;
		$application->setContext($container);
		$application->catchExceptions = Environment::isProduction();
		return $application;
	}



	/**
	 * @return Nette\Http\Request
	 */
	public static function createHttpRequest()
	{
		$factory = new Nette\Http\RequestFactory;
		$factory->setEncoding('UTF-8');
		return $factory->createHttpRequest();
	}



	/**
	 * @return Nette\Http\Context
	 */
	public static function createHttpContext(IContainer $container)
	{
		return new Nette\Http\Context(
			$container->getService('Nette\\Web\\IHttpRequest'),
			$container->getService('Nette\\Web\\IHttpResponse')
		);
	}



	/**
	 * @return Nette\Http\Session
	 */
	public static function createHttpSession(IContainer $container)
	{
		return new Nette\Http\Session(
			$container->getService('Nette\\Web\\IHttpRequest'),
			$container->getService('Nette\\Web\\IHttpResponse')
		);
	}



	/**
	 * @return Nette\Http\User
	 */
	public static function createHttpUser(IContainer $container)
	{
		return new Nette\Http\User(clone $container);
	}



	/**
	 * @return Nette\Caching\IStorage
	 */
	public static function createCacheStorage(IContainer $container)
	{
		$dir = Environment::getVariable('tempDir') . '/cache';
		umask(0000);
		@mkdir($dir, 0777); // @ - directory may exists
		return new Nette\Caching\Storages\FileStorage($dir, $container->getService('Nette\\Caching\\ICacheJournal'));
	}



	/**
	 * @return Nette\Caching\IStorage
	 */
	public static function createTemplateCacheStorage()
	{
		$dir = Environment::getVariable('tempDir') . '/cache';
		umask(0000);
		@mkdir($dir, 0777); // @ - directory may exists
		return new Nette\Templating\PhpFileStorage($dir);
	}



	/**
	 * @return Nette\Caching\Storages\IJournal
	 */
	public static function createCacheJournal()
	{
		return new Nette\Caching\Storages\FileJournal(Environment::getVariable('tempDir'));
	}



	/**
	 * @return Nette\Mail\IMailer
	 */
	public static function createMailer(IContainer $container, array $options = NULL)
	{
		if (empty($options['smtp'])) {
			return new Nette\Mail\SendmailMailer;
		} else {
			return new Nette\Mail\SmtpMailer($options);
		}
	}



	/**
	 * @return Nette\Loaders\RobotLoader
	 */
	public static function createRobotLoader(IContainer $container, array $options = NULL)
	{
		$loader = new Nette\Loaders\RobotLoader;
		$loader->autoRebuild = isset($options['autoRebuild']) ? $options['autoRebuild'] : !Environment::isProduction();
		$loader->setCacheStorage($container->getService('Nette\\Caching\\ICacheStorage'));
		if (isset($options['directory'])) {
			$loader->addDirectory($options['directory']);
		} else {
			foreach (array('appDir', 'libsDir') as $var) {
				if ($dir = Environment::getVariable($var, NULL)) {
					$loader->addDirectory($dir);
				}
			}
		}
		$loader->register();
		return $loader;
	}

}
