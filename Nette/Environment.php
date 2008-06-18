<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com/
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com/
 * @category   Nette
 * @package    Nette
 */

/*namespace Nette;*/


/**/define('__DIR__', dirname(__FILE__));/**/


/**
 * Nette environment and configuration.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette
 * @version    $Revision$ $Date$
 */
final class Environment
{
	/** environments */
	const DEVELOPMENT = 'development';
	const PRODUCTION = 'production';
	const CONSOLE = 'console';
	const LAB = 'lab';

	/** modes: */
	const DEBUG = 'debug';
	const PERFORMANCE = 'performance';

	/** variables */
	const LANG = 'lang';

	/** @var string */
	private static $name;

	/** @var string  the mode of current application */
	private static $mode = array();

	/** @var Config */
	private static $config;

	/** @var IServiceLocator */
	private static $locator;

	/** @var array */
	private static $vars = array(
		'encoding' => array('UTF-8', FALSE),
		'lang' => array('en', FALSE),
		'netteDir' => array(__DIR__, FALSE),
		'cacheBase' => array('%tempDir%/cache-', TRUE),
		'tempDir' => array('%appDir%/temp', TRUE),
		'logDir' => array('%appDir%/log', TRUE),
		'libsDir' => array('%appDir%/libs', TRUE),
		'templatesDir' => array('%appDir%/templates', TRUE),
		'presentersDir' => array('%appDir%/presenters', TRUE),
		'componentsDir' => array('%appDir%/components', TRUE),
		'modelsDir' => array('%appDir%/models', TRUE),
	);

	public static $defaultServices = array(
		'Nette::IServiceLocator' => /*Nette::*/'ServiceLocator',
		'Nette::Web::IHttpRequest' => 'Nette::Web::HttpRequest',
		'Nette::Web::IHttpResponse' => 'Nette::Web::HttpResponse',
		'Nette::Application::IRouter' => 'Nette::Application::MultiRouter',
		'Nette::Caching::ICacheStorage' => array(__CLASS__, 'factoryCacheStorage'),
		'Nette::Configurator' => 'Nette::Configurator',
	);



	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new /*::*/LogicException("Cannot instantiate static class " . get_class($this));
	}



	/********************* environment name and modes ****************d*g**/



	/**
	 * Sets the current environment name.
	 * @param  string
	 * @return void
	 * @throws ::InvalidStateException
	 */
	public static function setName($name)
	{
		if (self::$name === NULL) {
			self::$name = (string) $name;
			//if (!defined('ENVIRONMENT')) define('ENVIRONMENT', self::$name);

		} else {
			throw new /*::*/InvalidStateException('Environment name has been already set.');
		}
	}



	/**
	 * Returns the current environment name.
	 * @return string
	 */
	public static function getName()
	{
		if (self::$name === NULL) {
			$configurator = self::getService('Nette::Configurator');
			self::setName($configurator->detectMode('environment'));
		}
		return self::$name;
	}



	/**
	 * Sets the mode.
	 *
	 * @param  string mode identifier
	 * @param  bool   set or unser
	 * @return void
	 */
	public static function setMode($mode, $flag = TRUE)
	{
		self::$mode[$mode] = (bool) $flag;
	}



	/**
	 * Returns the mode.
	 *
	 * @param  string mode identifier
	 * @return bool
	 */
	public static function getMode($mode)
	{
		if (isset(self::$mode[$mode])) {
			return self::$mode[$mode];

		} else {
			$configurator = self::getService('Nette::Configurator');
			return self::$mode[$mode] = $configurator->detectMode($mode);
		}
	}



	/**
	 * Detects console (non-HTTP) mode.
	 * @return bool
	 */
	public static function isConsole()
	{
		return self::getMode('console');
	}



	/**
	 * Determines if the debugger is active.
	 * @return bool
	 */
	public static function isDebugging()
	{
		return self::getMode('debug');
	}



	/********************* environment variables ****************d*g**/



	/**
	 * Sets the environment variable.
	 * @param  string
	 * @param  mixed
	 * @param  bool
	 * @return void
	 */
	public static function setVariable($name, $value, $expand = TRUE)
	{
		self::$vars[$name] = array($value, (bool) $expand);
	}



	/**
	 * Returns the value of an environment variable or $default if there is no element set.
	 * @param  string
	 * @param  mixed  default value to use if key not found
	 * @return mixed
	 */
	public static function getVariable($name, $default = NULL)
	{
		if (isset(self::$vars[$name])) {
			list($var, $exp) = self::$vars[$name];
			if ($exp) {
				$var = self::expand($var);
				self::$vars[$name] = array($var, FALSE);
			}
			return $var;

		} else {
			// convert from camelCaps (or PascalCaps) to ALL_CAPS
			$const = strtoupper(preg_replace('#(.)([A-Z]+)#', '$1_$2', $name));
			$list = get_defined_constants(TRUE);
			if (isset($list['user'][$const])) {
				self::$vars[$name] = array($list['user'][$const], FALSE);
				return $list['user'][$const];

			} else {
				return $default;
			}
		}
	}



	/**
	 * Define one or more variables as constants.
	 * @param  string|array
	 * @return void
	 */
	public static function exportConstant($names)
	{
		if (!is_array($names)) {
			$names = func_get_args();
		}

		foreach ($names as $name) {
			$const = strtoupper(preg_replace('#(.)([A-Z]+)#', '$1_$2', $name));
			define($const, self::getVariable($name));
		}
	}



	/**
	 * Returns expanded variable.
	 * @param  string
	 * @return string
	 */
	public static function expand($var)
	{
		if (is_string($var) && strpos($var, '%') !== FALSE) {
			return preg_replace_callback('#%([a-z0-9_-]*)%#i', array(__CLASS__, 'expandCb'), $var);
		}
		return $var;
	}



	/**
	 * @see self::expand()
	 * @param  array
	 * @return string
	 */
	private static function expandCb($m)
	{
		list(, $var) = $m;
		if ($var === '') return '%';

		static $livelock;
		if (isset($livelock[$var])) {
			throw new /*::*/InvalidStateException("Circular reference detected for variables: "
				. implode(', ', array_keys($livelock)) . ".");
		}

		try {
			$livelock[$var] = TRUE;
			$val = self::getVariable($var);
			unset($livelock[$var]);
		} catch (Exception $e) {
			$livelock = array();
			throw $e;
		}

		if ($val === NULL) {
			throw new /*::*/InvalidStateException("Unknown environment variable '$var'.");
		}
		return $val;
	}



	/********************* service locator ****************d*g**/



	/**
	 * Get initial instance of service locator (experimental).
	 * @return IServiceLocator
	 */
	public static function getServiceLocator()
	{
		if (self::$locator === NULL) {
			self::$locator = new self::$defaultServices['Nette::IServiceLocator'];
			foreach (self::$defaultServices as $name => $service) {
				self::$locator->addService($service, $name);
			}
		}
		return self::$locator;
	}



	/**
	 * Gets the service object of the specified type.
	 * @param  string service name
	 * @param  bool
	 * @return mixed
	 */
	static public function getService($name, $need = TRUE)
	{
		return self::getServiceLocator()->getService($name, $need);
	}



	/**
	 * @return Nette::Web::IHttpRequest
	 */
	public static function getHttpRequest()
	{
		return self::getServiceLocator()->getService('Nette::Web::IHttpRequest');
	}



	/**
	 * @return Nette::Web::IHttpResponse
	 */
	public static function getHttpResponse()
	{
		return self::getServiceLocator()->getService('Nette::Web::IHttpResponse');
	}



	/**
	 * @return Nette::Application::Application
	 */
	public static function getApplication()
	{
		return self::getServiceLocator()->getService('Nette::Application::Application');
	}



	/**
	 * @return Nette::Security::IUser
	 */
	public static function getUser()
	{
		return self::getServiceLocator()->getService('Nette::Security::User');
	}



	/********************* service factories ****************d*g**/



	/**
	 * @param  string
	 * @return Nette::Caching::Cache
	 */
	public static function getCache($namespace = '')
	{
		return new /*Nette::Caching::*/Cache(
			self::getService('Nette::Caching::ICacheStorage'),
			$namespace
		);
	}



	/**
	 * @param  string
	 * @return Nette::Caching::ICacheStorage
	 */
	public static function factoryCacheStorage()
	{
		return new /*Nette::Caching::*/FileStorage(self::getVariable('cacheBase'));
	}



	/**
	 * Returns instance of session namespace.
	 * @param  string
	 * @return Nette::Web::SessionNamespace
	 */
	public static function getSession($name = 'default')
	{
		return /*Nette::*/Session::getNamespace($name);
	}



	/********************* global configuration ****************d*g**/



	/**
	 * Loads global configuration from file and process it.
	 * @param  string|Config  file name or Config object
	 * @param  bool
	 * @return Config
	 */
	public static function loadConfig($file = NULL, $useCache = NULL)
	{
		$configurator = self::getService('Nette::Configurator');
		$configurator->useCache = $useCache;
		return self::$config = $configurator->loadConfig($file);
	}



	/**
	 * Returns the global configuration.
	 * @return Config
	 */
	public static function getConfig($key = NULL, $default = NULL)
	{
		if (func_num_args()) {
			return self::$config->get($key, $default);
		} else {
			return self::$config;
		}
	}



	/**
	 * Caching helper.
	 * @param  array
	 * @return array
	 */
	public static function swapState($state)
	{
		if ($state === NULL) {
			return array(self::$config, self::$vars, self::$locator);
		} else {
			list(self::$config, self::$vars, self::$locator) = $state;
		}
	}

}
