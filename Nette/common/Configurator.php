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

		defined('WWW_DIR') && $this->container->parameters['wwwDir'] = realpath(WWW_DIR);
		defined('APP_DIR') && $this->container->parameters['appDir'] = realpath(APP_DIR);
		defined('LIBS_DIR') && $this->container->parameters['libsDir'] = realpath(LIBS_DIR);
		defined('TEMP_DIR') && $this->container->parameters['tempDir'] = realpath(TEMP_DIR);
		$this->container->parameters['productionMode'] = self::detectProductionMode();
		$this->container->parameters['consoleMode'] = PHP_SAPI === 'cli';
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
				$section = $container->parameters['productionMode'] ? Environment::PRODUCTION : Environment::DEVELOPMENT;
			}
		}

		$cache = new Cache($container->templateCacheStorage, 'Nette.Configurator');
		$cacheKey = array((array) $container->parameters, $file, $section);
		$cached = $cache->load($cacheKey);
		if ($cached) {
			require $cached['file'];
			fclose($cached['handle']);
			return $this->container;
		}

		$config = Nette\Config\Config::fromFile($file, $section);
		$code = "<?php\n// source file $file\n\n";

		// obsolete and deprecated structures
		foreach (array('service' => 'services', 'variable' => 'parameters', 'variables' => 'parameters', 'mode' => 'parameters', 'const' => 'constants') as $old => $new) {
			if (isset($config[$old])) {
				throw new Nette\DeprecatedException(basename($file) . ": Section '$old' is deprecated; use '$new' instead.");
			}
		}
		if (isset($config['services'])) {
			foreach ($config['services'] as $key => $def) {
				foreach (array('option' => 'arguments', 'methods' => 'calls') as $old => $new) {
					if (isset($def[$old])) {
						throw new Nette\DeprecatedException(basename($file) . ": Section '$old' in service definition is deprecated; refactor it into '$new'.");
					}
				}
			}
		}


		// consolidate parameters
		if (!isset($config['parameters'])) {
			$config['parameters'] = array();
		}
		foreach ($config as $key => $value) {
			if (!in_array($key, array('parameters', 'services', 'php', 'constants'))) {
				$config['parameters'][$key] = $value;
			}
		}

		// pre-expand parameters at compile-time
		$parameters = $config['parameters'];
		array_walk_recursive($config, function(&$val) use ($parameters) {
			$val = Configurator::preExpand($val, $parameters);
		});

		// add parameters
		foreach ($config['parameters'] as $key => $value) {
			$code .= $this->generateCode('$container->parameters[?] = ?', $key, $value);
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
		if (isset($config['constants'])) {
			foreach ($config['constants'] as $key => $value) {
				$code .= $this->generateCode('define', $key, $value);
			}
		}

		// process services
		if (isset($config['services'])) {
			$builder = new DI\ContainerBuilder;
			foreach ($config['services'] as $key => $def) {
				if (is_array($def)) {
					$definition = $builder->addDefinition($key, isset($def['class']) ? $def['class'] : NULL, !empty($def['prefer']));

					if (isset($def['arguments'])) {
						$definition->setArguments(array_diff($def['arguments'], array('...')));
					}

					if (isset($def['calls'])) {
						foreach ($def['calls'] as $item) {
							$definition->addCall($item[0], isset($item[1]) ? array_diff($item[1], array('...')) : array());
						}
					}

					if (isset($def['factory'])) {
						$definition->setFactory($def['factory']);
						if (!$definition->arguments) {
							$definition->arguments[] = '@container';
						}
					} elseif (method_exists(get_called_class(), "createService$key") && !isset($def['factory']) && !isset($def['class'])) {
						$definition->setFactory(array(get_called_class(), "createService$key"));
					}

					if (!empty($def['run'])) {
						$definition->addTag('run');
					}

					if (isset($def['tags'])) {
						foreach ($def['tags'] as $tag => $attrs) {
							if (is_int($tag) && is_string($attrs)) {
								$definition->addTag($attrs);
							} else {
								$definition->addTag($tag, $attrs);
							}
						}
					}

					$def = array_diff(array_keys($def), array('class', 'prefer', 'arguments', 'calls', 'factory', 'run', 'tags'));
					if ($def) {
						throw new Nette\InvalidStateException("Unknown key '" . implode("', '", $def) . "' in definition of service '$key'.");
					}

				} elseif (is_scalar($def)) {
					$builder->addDefinition($key, $def);
				}
			}

			$code .= $builder->generateCode();
			// auto-start services
			foreach ($builder->findByTag("run") as $name => $foo) {
				$code .= $this->generateCode('$container->getService(?)', $name);
			}
		}

		// pre-loading
		$code .= self::preloadEnvironment($container);

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
		array_walk_recursive($args, function(&$val) {
			if (is_string($val) && strpos($val, '%') !== FALSE) {
				if (preg_match('#^%([\w-]+)%$#', $val)) {
					$val = new Nette\Utils\PhpGenerator\PhpLiteral('$container->parameters[' . strtr($val, '%', "'") . ']');
				} else {
					$val = new Nette\Utils\PhpGenerator\PhpLiteral('$container->expand(' . Nette\Utils\PhpGenerator\Helpers::dump($val) . ')');
				}
			}
		});
		return call_user_func_array('Nette\Utils\PhpGenerator\Helpers::generate', $args) . "\n\n";
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

		if (Nette\Application\UI\Presenter::$invalidLinkMode === NULL) {
			Nette\Application\UI\Presenter::$invalidLinkMode = $container->parameters['productionMode']
				? Nette\Application\UI\Presenter::INVALID_LINK_SILENT
				: Nette\Application\UI\Presenter::INVALID_LINK_WARNING;
		}

		$class = isset($options['class']) ? $options['class'] : 'Nette\Application\Application';
		$application = new $class($context);
		$application->catchExceptions = $container->parameters['productionMode'];
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
			isset($container->parameters['appDir']) ? $container->parameters['appDir'] : NULL,
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
		if (!isset($container->parameters['tempDir'])) {
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
		if (!isset($container->parameters['tempDir'])) {
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
		return new Nette\Caching\Storages\FileJournal($container->parameters['tempDir']);
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
		$loader->autoRebuild = isset($options['autoRebuild']) ? $options['autoRebuild'] : !$container->parameters['productionMode'];
		$loader->setCacheStorage($container->cacheStorage);
		if (isset($options['directory'])) {
			$loader->addDirectory($options['directory']);
		} else {
			foreach (array('appDir', 'libsDir') as $var) {
				if (isset($container->parameters[$var])) {
					$loader->addDirectory($container->parameters[$var]);
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
