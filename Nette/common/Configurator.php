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
	Nette\Caching\Cache,
	Nette\DI;



/**
 * Initial system DI container generator.
 *
 * @author     David Grudl
 */
class Configurator extends Object
{
	public static $instance;

	/** @var string */
	public $defaultConfigFile = '%appDir%/config.neon';

	/** @var DI\Container */
	private $container;



	public function __construct($containerClass = 'Nette\DI\Container')
	{
		self::$instance = $this;
		$this->container = new $containerClass;
		$this->container->addService('container', $this->container);

		foreach (get_class_methods($this) as $name) {
			if (substr($name, 0, 13) === 'createService' ) {
				$this->container->addService(strtolower($name[13]) . substr($name, 14), array(get_called_class(), $name));
			}
		}

		defined('WWW_DIR') && $this->container->params['wwwDir'] = realpath(WWW_DIR);
		defined('APP_DIR') && $this->container->params['appDir'] = realpath(APP_DIR);
		defined('LIBS_DIR') && $this->container->params['libsDir'] = realpath(LIBS_DIR);
		defined('TEMP_DIR') && $this->container->params['tempDir'] = realpath(TEMP_DIR);
		$this->container->params['productionMode'] = self::detectProductionMode();
		$this->container->params['consoleMode'] = PHP_SAPI === 'cli';
	}



	/**
	 * Get initial instance of DI container.
	 * @return DI\Container
	 */
	public function getContainer()
	{
		return $this->container;
	}



	/**
	 * Loads configuration from file and process it.
	 * @return DI\Container
	 */
	public function loadConfig($file, $section = NULL)
	{
		if ($file === NULL) {
			$file = $this->defaultConfigFile;
		}
		$container = $this->container;
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

		$cache = new Cache($container->templateCacheStorage, 'Nette.Configurator');
		$cacheKey = array((array) $container->params, $file, $section);
		$cached = $cache->load($cacheKey);
		if ($cached) {
			require $cached['file'];
			fclose($cached['handle']);
			return $this->container;
		}

		$config = Nette\Config\Config::fromFile($file, $section);
		$code = "<?php\n// source file $file\n\n";

		// back compatibility with singular names
		foreach (array('service', 'variable') as $item) {
			if (isset($config[$item])) {
				trigger_error(basename($file) . ": Section '$item' is deprecated; use plural form '{$item}s' instead.", E_USER_WARNING);
				$config[$item . 's'] = $config[$item];
				unset($config[$item]);
			}
		}

		// process services
		if (isset($config['services'])) {
			foreach ($config['services'] as $key => & $def) {
				if (preg_match('#^Nette\\\\.*\\\\I?([a-zA-Z]+)$#', strtr($key, '-', '\\'), $m)) { // back compatibility
					$m[1][0] = strtolower($m[1][0]);
					trigger_error(basename($file) . ": service name '$key' has been renamed to '$m[1]'", E_USER_WARNING);
					$key = $m[1];
				}

				if (is_array($def)) {
					if (method_exists(get_called_class(), "createService$key") && !isset($def['factory']) && !isset($def['class'])) {
						$def['factory'] = array(get_called_class(), "createService$key");
					}

					if (isset($def['option'])) {
						$def['arguments'][] = $def['option'];
					}

					if (!empty($def['run'])) {
						$def['tags'] = array('run');
					}
				}
			}
			$builder = new DI\ContainerBuilder;
			/**/$code .= $builder->generateCode($config['services']);/**/
			/*5.2* $code .= '$builder = new '.get_class($builder).'; $builder->addDefinitions($container, '.var_export($config['services'], TRUE).');';*/
			unset($config['services']);
		}

		// consolidate variables
		if (!isset($config['variables'])) {
			$config['variables'] = array();
		}
		foreach ($config as $key => $value) {
			if (!in_array($key, array('variables', 'services', 'php', 'const', 'mode'))) {
				$config['variables'][$key] = $value;
			}
		}

		// pre-expand variables at compile-time
		$variables = $config['variables'];
		array_walk_recursive($config, function(&$val) use ($variables) {
			$val = Configurator::preExpand($val, $variables);
		});

		// add variables
		foreach ($config['variables'] as $key => $value) {
			$code .= $this->generateCode('$container->params[?] = ?', $key, $value);
		}

		// PHP settings
		if (isset($config['php'])) {
			foreach ($config['php'] as $key => $value) {
				if (is_array($value)) { // back compatibility - flatten INI dots
					foreach ($value as $k => $v) {
						$code .= $this->configurePhp("$key.$k", $v);
					}
				} else {
					$code .= $this->configurePhp($key, $value);
				}
			}
		}

		// define constants
		if (isset($config['const'])) {
			foreach ($config['const'] as $key => $value) {
				$code .= $this->generateCode('define', $key, $value);
			}
		}

		// set modes - back compatibility
		if (isset($config['mode'])) {
			foreach ($config['mode'] as $mode => $state) {
				trigger_error(basename($file) . ": Section 'mode' is deprecated; use '{$mode}Mode' in section 'variables' instead.", E_USER_WARNING);
				$code .= $this->generateCode('$container->params[?] = ?', $mode . 'Mode', (bool) $state);
			}
		}

		// pre-loading
		$code .= self::preloadEnvironment($container);

		// auto-start services
		$code .= 'foreach ($container->getServiceNamesByTag("run") as $name => $foo) { $container->getService($name); }' . "\n";

		$cache->save($cacheKey, $code, array(
			Cache::FILES => $file,
		));

		Nette\Utils\LimitedScope::evaluate($code, array('container' => $container));
		return $this->container;
	}



	/********************* tools ****************d*g**/



	/**
	 * Detects production mode by IP address.
	 * @return bool
	 */
	public static function detectProductionMode()
	{
		$addrs = array();
		if (PHP_SAPI === 'cli') {
			$addrs[] = getHostByName(php_uname('n'));
		}
		else {
			if (!isset($_SERVER['SERVER_ADDR']) && !isset($_SERVER['LOCAL_ADDR'])) {
				return TRUE;
			}
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { // proxy server detected
				$addrs = preg_split('#,\s*#', $_SERVER['HTTP_X_FORWARDED_FOR']);
			}
			if (isset($_SERVER['REMOTE_ADDR'])) {
				$addrs[] = $_SERVER['REMOTE_ADDR'];
			}
			$addrs[] = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
		}
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
			return $this->generateCode('set_include_path', str_replace(';', PATH_SEPARATOR, $value));
		case 'ignore_user_abort':
			return $this->generateCode('ignore_user_abort', $value);
		case 'max_execution_time':
			return $this->generateCode('set_time_limit', $value);
		case 'date.timezone':
			return $this->generateCode('date_default_timezone_set', $value);
		}

		if (function_exists('ini_set')) {
			return $this->generateCode('ini_set', $name, $value);
		} elseif (ini_get($name) != $value && !Framework::$iAmUsingBadHost) { // intentionally ==
			throw new Nette\NotSupportedException('Required function ini_set() is disabled.');
		}
	}



	private static function generateCode($statement)
	{
		$args = func_get_args();
		unset($args[0]);
		foreach ($args as &$arg) {
			$arg = var_export($arg, TRUE);
			$arg = preg_replace("#(?<!\\\)'%([\w-]+)%'#", '\$container->params[\'$1\']', $arg);
			$arg = preg_replace("#(?<!\\\)'(?:[^'\\\]|\\\.)*%(?:[^'\\\]|\\\.)*'#", '\$container->expand($0)', $arg);
		}
		if (strpos($statement, '?') === FALSE) {
			return $statement .= '(' . implode(', ', $args) . ");\n\n";
		}
		$a = strpos($statement, '?');
		$i = 1;
		while ($a !== FALSE) {
			$statement = substr_replace($statement, $args[$i], $a, 1);
			$a = strpos($statement, '?', $a + strlen($args[$i]));
			$i++;
		}
		return $statement . ";\n\n";
	}



	/**
	 * Pre-expands %placeholders% in string.
	 * @internal
	 */
	public static function preExpand($s, array $params, $check = array())
	{
		if (!is_string($s)) {
			return $s;
		}

		$parts = preg_split('#%([\w.-]*)%#i', $s, -1, PREG_SPLIT_DELIM_CAPTURE);
		$res = '';
		foreach ($parts as $n => $part) {
			if ($n % 2 === 0) {
				$res .= str_replace('%', '%%', $part);

			} elseif ($part === '') {
				$res .= '%%';

			} elseif (isset($check[$part])) {
				throw new Nette\InvalidArgumentException('Circular reference detected for variables: ' . implode(', ', array_keys($check)) . '.');

			} else {
				try {
					$val = Nette\Utils\Arrays::get($params, explode('.', $part));
				} catch (Nette\InvalidArgumentException $e) {
					$res .= "%$part%";
					continue;
				}
				$val = self::preExpand($val, $params, $check + array($part => 1));
				if (strlen($part) + 2 === strlen($s)) {
					if (is_array($val)) {
						array_walk_recursive($val, function(&$val) use ($params, $check, $part) {
							$val = Configurator::preExpand($val, $params, $check + array($part => 1));
						});
					}
					return $val;
				}
				if (!is_scalar($val)) {
					throw new Nette\InvalidArgumentException("Unable to concatenate non-scalar parameter '$part' into '$s'.");
				}
				$res .= $val;
			}
		}
		return $res;
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
		$context->addService('router', $container->router);

		Nette\Application\UI\Presenter::$invalidLinkMode = $container->params['productionMode']
			? Nette\Application\UI\Presenter::INVALID_LINK_SILENT
			: Nette\Application\UI\Presenter::INVALID_LINK_WARNING;

		$class = isset($options['class']) ? $options['class'] : 'Nette\Application\Application';
		$application = new $class($context);
		$application->catchExceptions = $container->params['productionMode'];
		if ($container->session->exists()) {
		$application->onStartup[] = function() use ($container) {
				$container->session->start(); // opens already started session
			};
		}
		return $application;
	}



	/**
	 * @return Nette\Application\IPresenterFactory
	 */
	public static function createServicePresenterFactory(DI\Container $container)
	{
		return new Nette\Application\PresenterFactory(
			isset($container->params['appDir']) ? $container->params['appDir'] : NULL,
			$container
		);
	}



	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createServiceRouter(DI\Container $container)
	{
		return new Nette\Application\Routers\RouteList;
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
	 * @return Nette\Http\Response
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
	public static function createServiceSession(DI\Container $container, array $options = NULL)
	{
		$session = new Nette\Http\Session($container->httpRequest, $container->httpResponse);
		$session->setOptions((array) $options);
		if (isset($options['expiration'])) {
			$session->setExpiration($options['expiration']);
		}
		return $session;
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
		if (!isset($container->params['tempDir'])) {
			throw new Nette\InvalidStateException("Service cacheStorage requires that parameter 'tempDir' contains path to temporary directory.");
		}
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
		if (!isset($container->params['tempDir'])) {
			throw new Nette\InvalidStateException("Service templateCacheStorage requires that parameter 'tempDir' contains path to temporary directory.");
		}
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



	public static function preloadEnvironment(DI\Container $container)
	{
		$code = '';
		$dir = $container->expand('%tempDir%/cache');
		umask(0000);
		@mkdir($dir, 0777); // @ - directory may exists

		// checks whether directory is writable
		$uniq = uniqid('_', TRUE);
		umask(0000);
		if (!@mkdir("$dir/$uniq", 0777)) { // @ - is escalated to exception
			throw new Nette\InvalidStateException("Unable to write to directory '$dir'. Make this directory writable.");
		}

		// tests subdirectory mode
		$useDirs = @file_put_contents("$dir/$uniq/_", '') !== FALSE; // @ - error is expected
		@unlink("$dir/$uniq/_");
		@rmdir("$dir/$uniq"); // @ - directory may not already exist

		$code .= self::generateCode('Nette\Caching\Storages\FileStorage::$useDirectories = ?', $useDirs);
		return $code;
	}

}
