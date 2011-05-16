<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette;

use Nette,
	Nette\DI;



/**
 * Initial system DI container generator.
 *
 * @author     David Grudl
 */
class Configurator extends Object
{
	/** @var string */
	public $defaultConfigFile = '%appDir%/config.neon';



	/**
	 * Get initial instance of DI container.
	 * @return DI\Container
	 */
	public static function createContainer($containerClass = 'Nette\DI\Container')
	{
		$container = new $containerClass;

		foreach (get_class_methods(__CLASS__) as $name) {
			if ($name !== __FUNCTION__ && substr($name, 0, 13) === 'createService' ) {
				$container->addService(strtolower($name[13]) . substr($name, 14), array(__CLASS__, $name));
			}
		}

		$container->params = new ArrayHash;
		defined('APP_DIR') && $container->params['appDir'] = realpath(APP_DIR);
		defined('LIBS_DIR') && $container->params['libsDir'] = realpath(LIBS_DIR);
		defined('TEMP_DIR') && $container->params['tempDir'] = realpath(TEMP_DIR);
		$container->params['productionMode'] = self::detectProductionMode();
		$container->params['consoleMode'] = PHP_SAPI === 'cli';

		return $container;
	}



	/**
	 * Loads configuration from file and process it.
	 * @return void
	 */
	public function loadConfig(DI\Container $container, $file, $section = NULL)
	{
		if ($file === NULL) {
			$file = $this->defaultConfigFile;
		}
		$file = $container->expand($file);
		if (!is_file($file)) {
			$file = preg_replace('#\.neon$#', '.ini', $file); // back compatibility
		}
		if ($section === NULL) {
			if (PHP_SAPI === 'cli') {
				$section = Environment::CONSOLE;
			} else {
				$section = $container->params['productionMode'] ? Environment::PRODUCTION : Environment::DEVELOPMENT;
		}
		}
		$config = Nette\Config\Config::fromFile($file, $section);

		// back compatibility with singular names
		foreach (array('service', 'variable') as $item) {
			if (isset($config[$item])) {
				trigger_error(basename($file) . ": Section '$item' is deprecated; use plural form '{$item}s' instead.", E_USER_WARNING);
				$config[$item . 's'] = $config[$item];
				unset($config[$item]);
			}
		}

		// add expanded variables
		while (!empty($config['variables'])) {
			$old = $config['variables'];
			foreach ($config['variables'] as $key => $value) {
				try {
					$container->params[$key] = $container->expand($value);
					unset($config['variables'][$key]);
				} catch (Nette\InvalidArgumentException $e) {}
			}
			if ($old === $config['variables']) {
				throw new InvalidStateException("Circular reference detected for variables: "
						. implode(', ', array_keys($old)) . ".");
			}
		}
		unset($config['variables']);

		// process services
		if (isset($config['services'])) {
			foreach ($config['services'] as $key => & $def) {
				if (preg_match('#^Nette\\\\.*\\\\I?([a-zA-Z]+)$#', strtr($key, '-', '\\'), $m)) { // back compatibility
					$m[1][0] = strtolower($m[1][0]);
					trigger_error(basename($file) . ": service name '$key' has been renamed to '$m[1]'", E_USER_WARNING);
					$key = $m[1];
				}

				if (method_exists(__CLASS__, "createService$key")) {
					$container->removeService($key);
					if (!is_scalar($def) && !isset($def['factory']) && !isset($def['class'])) {
						$def['factory'] = array(__CLASS__, "createService$key");
					}
				}

				if (!is_scalar($def) && isset($def['option'])) {
					$def['arguments'][] = $def['option'];
				}

				if (!is_scalar($def) && !empty($def['run'])) {
					$def['tags'] = array('run');
				}
			}
			$builder = new DI\ContainerBuilder;
			$builder->addDefinitions($container, $config['services']);
			unset($config['services']);
		}

		// expand variables
		array_walk_recursive($config, function(&$val) {
			$val = Environment::expand($val);
		});

		// PHP settings
		if (isset($config['php'])) {
			foreach ($config['php'] as $key => $value) {
				if (is_array($value)) { // back compatibility - flatten INI dots
					foreach ($value as $k => $v) {
						$this->configurePhp("$key.$k", $v);
					}
				} else {
					$this->configurePhp($key, $value);
				}
			}
			unset($config['php']);
		}

		// define constants
		if (isset($config['const'])) {
			foreach ($config['const'] as $key => $value) {
				define($key, $value);
			}
			unset($config['const']);
		}

		// set modes - back compatibility
		if (isset($config['mode'])) {
			trigger_error(basename($file) . ": Section 'mode' is deprecated.", E_USER_WARNING);
			foreach ($config['mode'] as $mode => $state) {
				$container->params[$mode . 'Mode'] = (bool) $state;
			}
			unset($config['mode']);
		}

		// other
		foreach ($config as $key => $value) {
			$container->params[$key] = is_array($value) ? Nette\ArrayHash::from($value) : $value;
		}

		// auto-start services
		foreach ($container->getServiceNamesByTag('run') as $name => $foo) {
			$container->getService($name);
		}
	}



	/********************* tools ****************d*g**/



	public function detect($name)
	{
		switch ($name) {
		case 'environment':
			if ($this->detect('console')) {
				return Environment::CONSOLE;
			} else {
				return Environment::getMode('production') ? Environment::PRODUCTION : Environment::DEVELOPMENT;
			}

		case 'production':
			return self::detectProductionMode();

		case 'console':
			return PHP_SAPI === 'cli';
		}
	}



	/**
	 * Detects production mode by IP address.
	 * @return bool
	 */
	public static function detectProductionMode()
	{
		if (!isset($_SERVER['SERVER_ADDR']) && !isset($_SERVER['LOCAL_ADDR'])) {
			return TRUE;
		}
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
	}



	public function configurePhp($name, $value)
	{
		if (!is_scalar($value)) {
			throw new Nette\InvalidStateException("Configuration value for directive '$name' is not scalar.");
		}

		switch ($name) {
		case 'include_path':
			set_include_path(str_replace(';', PATH_SEPARATOR, $value));
			return;
		case 'ignore_user_abort':
			ignore_user_abort($value);
			return;
		case 'max_execution_time':
			set_time_limit($value);
			return;
		case 'date.timezone':
			date_default_timezone_set($value);
			// intentionally call ini_set, PHP bug #47466
		}

		if (function_exists('ini_set')) {
			ini_set($name, $value);
		} elseif (ini_get($name) != $value) { // intentionally ==
			throw new Nette\NotSupportedException('Required function ini_set() is disabled.');
		}
	}



	/********************* service factories ****************d*g**/



	/**
	 * @return Nette\Application\Application
	 */
	public static function createServiceApplication(DI\Container $container, array $options = NULL)
	{
		$context = new DI\Container;
		$context->addService('httpRequest', $container->httpRequest);
		$context->addService('httpResponse', $container->httpResponse);
		$context->addService('session', $container->session);
		$context->addService('presenterFactory', $container->presenterFactory);
		$context->addService('router', 'Nette\Application\Routers\RouteList');

		Nette\Application\UI\Presenter::$invalidLinkMode = $container->params['productionMode']
			? Nette\Application\UI\Presenter::INVALID_LINK_SILENT
			: Nette\Application\UI\Presenter::INVALID_LINK_WARNING;

		$class = isset($options['class']) ? $options['class'] : 'Nette\Application\Application';
		$application = new $class($context);
		$application->catchExceptions = $container->params['productionMode'];
		return $application;
	}



	/**
	 * @return Nette\Application\IPresenterFactory
	 */
	public static function createServicePresenterFactory(DI\Container $container)
	{
		return new Nette\Application\PresenterFactory($container->params['appDir'], $container);
	}



	/**
	 * @return Nette\Http\Request
	 */
	public static function createServiceHttpRequest()
	{
		$factory = new Nette\Http\RequestFactory;
		$factory->setEncoding('UTF-8');
		return $factory->createHttpRequest();
	}



	/**
	 * @return Nette\Http\Request
	 */
	public static function createServiceHttpResponse()
	{
		return new Nette\Http\Response;
	}



	/**
	 * @return Nette\Http\Context
	 */
	public static function createServiceHttpContext(DI\Container $container)
	{
		return new Nette\Http\Context($container->httpRequest, $container->httpResponse);
	}



	/**
	 * @return Nette\Http\Session
	 */
	public static function createServiceSession(DI\Container $container)
	{
		return new Nette\Http\Session($container->httpRequest, $container->httpResponse);
	}



	/**
	 * @return Nette\Http\User
	 */
	public static function createServiceUser(DI\Container $container)
	{
		$context = new DI\Container;
		// copies services from $container and preserves lazy loading
		$context->addService('authenticator', function() use ($container) {
			return $container->authenticator;
		});
		$context->addService('authorizator', function() use ($container) {
			return $container->authorizator;
		});
		$context->addService('session', $container->session);
		return new Nette\Http\User($context);
	}



	/**
	 * @return Nette\Caching\IStorage
	 */
	public static function createServiceCacheStorage(DI\Container $container)
	{
		$dir = $container->expand('%tempDir%/cache');
		umask(0000);
		@mkdir($dir, 0777); // @ - directory may exists
		return new Nette\Caching\Storages\FileStorage($dir, $container->cacheJournal);
	}



	/**
	 * @return Nette\Caching\IStorage
	 */
	public static function createServiceTemplateCacheStorage(DI\Container $container)
	{
		$dir = $container->expand('%tempDir%/cache');
		umask(0000);
		@mkdir($dir, 0777); // @ - directory may exists
		return new Nette\Caching\Storages\PhpFileStorage($dir);
	}



	/**
	 * @return Nette\Caching\Storages\IJournal
	 */
	public static function createServiceCacheJournal(DI\Container $container)
	{
		return new Nette\Caching\Storages\FileJournal($container->params['tempDir']);
	}



	/**
	 * @return Nette\Mail\IMailer
	 */
	public static function createServiceMailer(DI\Container $container, array $options = NULL)
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
	public static function createServiceRobotLoader(DI\Container $container, array $options = NULL)
	{
		$loader = new Nette\Loaders\RobotLoader;
		$loader->autoRebuild = isset($options['autoRebuild']) ? $options['autoRebuild'] : !$container->params['productionMode'];
		$loader->setCacheStorage($container->cacheStorage);
		if (isset($options['directory'])) {
			$loader->addDirectory($options['directory']);
		} else {
			foreach (array('appDir', 'libsDir') as $var) {
				if (isset($container->params[$var])) {
					$loader->addDirectory($container->params[$var]);
				}
			}
		}
		$loader->register();
		return $loader;
	}

}
