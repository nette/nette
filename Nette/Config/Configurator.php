<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Config;

use Nette,
	Nette\Caching\Cache,
	Nette\DI\ContainerBuilder,
	Nette\Utils\Validators;



/**
 * Initial system DI container generator.
 *
 * @author     David Grudl
 *
 * @property-read \SystemContainer $container
 */
class Configurator extends Nette\Object
{
	/** config file sections */
	const DEVELOPMENT = 'development',
		PRODUCTION = 'production',
		CONSOLE = 'console';

	/** @var Nette\DI\Container */
	private $container;

	/** @var array */
	private $params;



	public function __construct()
	{
		$this->params = $this->getDefaultParameters();
		Nette\Environment::setConfigurator($this); // back compatibility
	}



	/**
	 * Sets path to temporary directory.
	 * @return ServiceDefinition
	 */
	public function setCacheDirectory($path)
	{
		$this->params['tempDir'] = $path;
		return $this;
	}



	/**
	 * Adds new parameters. The %params% will be expanded.
	 * @return ServiceDefinition
	 */
	public function addParameters(array $params)
	{
		$this->params = $params + $this->params;
		return $this;
	}



	/**
	 * @return array
	 */
	protected function getDefaultParameters()
	{
		$trace = debug_backtrace(FALSE);
		return array(
			'appDir' => isset($trace[1]['file']) ? dirname($trace[1]['file']) : NULL,
			'wwwDir' => isset($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) : NULL,
			'productionMode' => static::detectProductionMode(),
			'consoleMode' => PHP_SAPI === 'cli',
		);
	}



	/**
	 * @return Nette\Loaders\RobotLoader
	 */
	public function createRobotLoader()
	{
		if (empty($this->params['tempDir'])) {
			throw new Nette\InvalidStateException("Set path to temporary directory using setCacheDirectory().");
		}
		$loader = new Nette\Loaders\RobotLoader;
		$loader->setCacheStorage(new Nette\Caching\Storages\FileStorage($this->params['tempDir']));
		$loader->autoRebuild = !$this->params['productionMode'];
		return $loader;
	}



	/**
	 * Returns system DI container.
	 * @return \SystemContainer
	 */
	public function getContainer()
	{
		if (!$this->container) {
			$this->createContainer();
		}
		return $this->container;
	}



	/**
	 * Loads configuration from file and process it.
	 * @return \SystemContainer
	 */
	public function loadConfig($file, $section = NULL)
	{
		if ($section === NULL) {
			if (PHP_SAPI === 'cli') {
				$section = self::CONSOLE;
			} else {
				$section = $this->params['productionMode'] ? self::PRODUCTION : self::DEVELOPMENT;
			}
		}

		$this->createContainer($file, $section);
		return $this->container;
	}



	private function createContainer($file = NULL, $section = NULL)
	{
		if ($this->container) {
			throw new Nette\InvalidStateException('Container has already been created. Make sure you did not call getContainer() before loadConfig().');
		}

		$this->params['environment'] = $section;

		if (!empty($this->params['tempDir'])) {
			$cache = new Cache(new Nette\Caching\Storages\PhpFileStorage($this->params['tempDir']), 'Nette.Configurator');
			$cacheKey = array($this->params, $file, $section);
			$cached = $cache->load($cacheKey);
			if (!$cached) {
				$loader = new Config;
				$config = $file ? $loader->load($file, $section) : array();
				$dependencies = $loader->getDependencies();
				$code = "<?php\n// source file $file $section\n\n"
					. $this->buildContainer($config, $dependencies);

				$cache->save($cacheKey, $code, array(
					Cache::FILES => $this->params['productionMode'] ? NULL : $dependencies,
				));
				$cached = $cache->load($cacheKey);
			}
			Nette\Utils\LimitedScope::load($cached['file']);

		} elseif ($file) {
			throw new Nette\InvalidStateException("Set path to temporary directory using setCacheDirectory().");

		} else {
			Nette\Utils\LimitedScope::evaluate('<?php ' . $this->buildContainer(array()));
		}

		$class = $this->formatContainerClassName();
		$this->container = new $class;
		$this->container->initialize();
	}



	private function buildContainer(array $config, array & $dependencies = array())
	{
		$this->checkCompatibility($config);

		$container = new ContainerBuilder;

		// merge and expand parameters
		if (isset($config['parameters'])) {
			$container->parameters = $config['parameters'];
		}
		$container->parameters += $this->params;

		$configExp = $container->expand($config);
		$this->configureCore($container,  $configExp);
		$this->parseDI($container, $config);

		// consolidate parameters
		foreach ($config as $key => $value) {
			if (!in_array($key, array('parameters', 'services', 'php', 'constants'))) {
				$container->parameters[$key] = $value;
			}
		}

		$class = $container->generateClass();
		$class->setName($this->formatContainerClassName());

		$initialize = $class->addMethod('initialize');

		// PHP settings
		if (isset($config['php'])) {
			$this->configurePhp($container, $class, $configExp['php']);
		}

		// define constants
		if (isset($config['constants'])) {
			$this->configureConstants($container, $class, $configExp['constants']);
		}

		// auto-start services
		foreach ($container->findByTag('run') as $name => $foo) {
			$initialize->addBody('$this->getService(?);', array($name));
		}

		// pre-loading
		if (isset($container->parameters['tempDir'])) {
			$initialize->addBody($this->checkTempDir($container->expand('%tempDir%/cache')));
		}

		$dependencies = array_merge($dependencies, $container->getDependencies());
		return (string) $class;
	}



	/**
	 * Parses 'services' and 'parameters' parts of config
	 * @return void
	 */
	public static function parseDI(ContainerBuilder $container, array $config)
	{
		if (isset($config['parameters'])) {
			$container->parameters = $config['parameters'] + $container->parameters;
		}

		if (isset($config['services'])) {
			uasort($config['services'], function($a, $b) {
				return strcmp(Config::isInheriting($a), Config::isInheriting($b));
			});
			foreach ($config['services'] as $name => $def) {
				if ($parent = Config::takeParent($def)) {
					$container->removeDefinition($name);
					$definition = $container->addDefinition($name);
					if ($parent !== Config::OVERWRITE) {
						foreach ($container->getDefinition($parent) as $k => $v) {
							$definition->$k = $v;
						}
					}
				} elseif ($container->hasDefinition($name)) {
					$definition = $container->getDefinition($name);
				} else {
					$definition = $container->addDefinition($name);
				}
				try {
					static::parseService($definition, $def);
				} catch (\Exception $e) {
					throw new Nette\DI\ServiceCreationException("Service $name: " . $e->getMessage()/**/, NULL, $e/**/);
				}
			}
		}
	}



	public static function parseService(Nette\DI\ServiceDefinition $definition, $config)
	{
		if (!is_array($config)) {
			$config = array('class' => $config);
		}

		$known = array('class', 'factory', 'arguments', 'autowired', 'setup', 'run', 'tags');
		if ($error = array_diff(array_keys($config), $known)) {
			throw new Nette\InvalidStateException("Unknown key '" . implode("', '", $error) . "' in definition of service.");
		}

		if (isset($config['class'])) {
			Validators::assertField($config, 'class', 'string');
			$definition->setClass($config['class']);
		}

		if (isset($config['factory'])) {
			Validators::assertField($config, 'factory', 'callable');
			$definition->setFactory($config['factory']);
			if (!isset($config['arguments'])) {
				$config['arguments'][] = '@container';
			}
		}

		if (isset($config['arguments'])) {
			Validators::assertField($config, 'arguments', 'array');
			$definition->setArguments(array_diff($config['arguments'], array('...')));
		}

		if (isset($config['setup'])) {
			Validators::assertField($config, 'setup', 'array');
			if (Config::takeParent($config['setup'])) {
				$definition->setup = array();
			}
			foreach ($config['setup'] as $member => $args) {
				if (is_int($member)) {
					Validators::assert($args, 'list:1..2', "setup item #$member");
					$member = $args[0];
					$args = isset($args[1]) ? $args[1] : NULL;
				}
				if (strpos($member, '$') === FALSE && $args !== NULL) {
					Validators::assert($args, 'array', "setup arguments for '$member'");
					$args = array_diff($args, array('...'));
				}
				$definition->addSetup($member, $args);
			}
		}

		if (isset($config['autowired'])) {
			Validators::assertField($config, 'autowired', 'bool|string');
			$definition->setAutowired($config['autowired']);
		}

		if (isset($config['run'])) {
			$config['tags']['run'] = (bool) $config['run'];
		}

		if (isset($config['tags'])) {
			Validators::assertField($config, 'tags', 'array');
			if (Config::takeParent($config['tags'])) {
				$definition->tags = array();
			}
			foreach ($config['tags'] as $tag => $attrs) {
				if (is_int($tag) && is_string($attrs)) {
					$definition->addTag($attrs);
				} else {
					$definition->addTag($tag, $attrs);
				}
			}
		}
	}



	private function checkCompatibility(array $config)
	{
		foreach (array('service' => 'services', 'variable' => 'parameters', 'variables' => 'parameters', 'mode' => 'parameters', 'const' => 'constants') as $old => $new) {
			if (isset($config[$old])) {
				throw new Nette\DeprecatedException(basename($file) . ": Section '$old' is deprecated; use '$new' instead.");
			}
		}
		if (isset($config['services'])) {
			foreach ($config['services'] as $key => $def) {
				foreach (array('option' => 'arguments', 'methods' => 'setup') as $old => $new) {
					if (is_array($def) && isset($def[$old])) {
						throw new Nette\DeprecatedException(basename($file) . ": Section '$old' in service definition is deprecated; refactor it into '$new'.");
					}
				}
			}
		}
	}



	public function formatContainerClassName()
	{
		return 'SystemContainer';
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
		} else {
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



	private function configurePhp(ContainerBuilder $container, Nette\Utils\PhpGenerator\ClassType $class, $config)
	{
		$initialize = $class->methods['initialize'];

		foreach ($config as $name => $value) { // back compatibility - flatten INI dots
			if (is_array($value)) {
				unset($config[$name]);
				foreach ($value as $k => $v) {
					$config["$name.$k"] = $v;
				}
			}
		}

		foreach ($config as $name => $value) {
			if (!is_scalar($value)) {
				throw new Nette\InvalidStateException("Configuration value for directive '$name' is not scalar.");

			} elseif ($name === 'include_path') {
				$initialize->addBody('set_include_path(?);', array(str_replace(';', PATH_SEPARATOR, $value)));

			} elseif ($name === 'ignore_user_abort') {
				$initialize->addBody('ignore_user_abort(?);', array($value));

			} elseif ($name === 'max_execution_time') {
				$initialize->addBody('set_time_limit(?);', array($value));

			} elseif ($name === 'date.timezone') {
				$initialize->addBody('date_default_timezone_set(?);', array($value));

			} elseif (function_exists('ini_set')) {
				$initialize->addBody('ini_set(?, ?);', array($name, $value));

			} elseif (ini_get($name) != $value && !Nette\Framework::$iAmUsingBadHost) { // intentionally ==
				throw new Nette\NotSupportedException('Required function ini_set() is disabled.');
			}
		}
	}



	private function configureConstants(ContainerBuilder $container, Nette\Utils\PhpGenerator\ClassType $class, $config)
	{
		$initialize = $class->methods['initialize'];
		foreach ($config as $name => $value) {
			$initialize->addBody('define(?, ?);', array($name, $value));
		}
	}



	private function configureCore(ContainerBuilder $container, & $config)
	{
		// cache
		$container->addDefinition('cacheJournal')
			->setClass('Nette\Caching\Storages\FileJournal', array('%tempDir%'));

		$container->addDefinition('cacheStorage')
			->setClass('Nette\Caching\Storages\FileStorage', array('%tempDir%/cache'));

		$container->addDefinition('templateCacheStorage')
			->setClass('Nette\Caching\Storages\PhpFileStorage', array('%tempDir%/cache'))
			->setAutowired(FALSE);

		// http
		$container->addDefinition('httpRequestFactory')
			->setClass('Nette\Http\RequestFactory')
			->addSetup('setEncoding', array('UTF-8'));

		$container->addDefinition('httpRequest')
			->setClass('Nette\Http\Request')
			->setFactory('@httpRequestFactory::createHttpRequest');

		$container->addDefinition('httpResponse')
			->setClass('Nette\Http\Response');

		$container->addDefinition('httpContext')
			->setClass('Nette\Http\Context');

		$session = $container->addDefinition('session')
			->setClass('Nette\Http\Session');

		if (isset($config['session'])) {
			Validators::assertField($config, 'session', 'array');
			$session->addSetup('setOptions', array($config['session']));
		}
		if (isset($config['session']['expiration'])) {
			$session->addSetup('setExpiration', array($config['session']['expiration']));
		}

		$container->addDefinition('user')
			->setClass('Nette\Http\User');

		// application
		$application = $container->addDefinition('application')
			->setClass('Nette\Application\Application')
			->addSetup('$catchExceptions', '%productionMode%');

		if (empty($config['productionMode'])) {
			$application->addSetup('Nette\Application\Diagnostics\RoutingPanel::initialize'); // enable routing debugger
		}

		$container->addDefinition('router')
			->setClass('Nette\Application\Routers\RouteList');

		$container->addDefinition('presenterFactory')
			->setClass('Nette\Application\PresenterFactory', array(
				isset($container->parameters['appDir']) ? $container->parameters['appDir'] : NULL
			));

		// mailer
		if (empty($config['mailer']['smtp'])) {
			$container->addDefinition('mailer')
				->setClass('Nette\Mail\SendmailMailer');
		} else {
			Validators::assertField($config, 'mailer', 'array');
			$container->addDefinition('mailer')
				->setClass('Nette\Mail\SmtpMailer', array($config['mailer']));
		}
	}



	/********************* service factories ****************d*g**/



	public function checkTempDir($dir)
	{
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

		return 'Nette\Caching\Storages\FileStorage::$useDirectories = ' . ($useDirs ? 'TRUE' : 'FALSE') . ";\n";
	}

}
