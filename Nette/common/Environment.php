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

use Nette;



/**
 * Nette environment and configuration.
 *
 * @author     David Grudl
 */
final class Environment
{
	/** environment name */
	const DEVELOPMENT = 'development',
		PRODUCTION = 'production',
		CONSOLE = 'console';

	/** @var Nette\DI\Configurator */
	private static $configurator;

	/** @var string  the mode of current application */
	private static $modes = array();

	/** @var \ArrayObject */
	private static $config;

	/** @var Nette\DI\IContext */
	private static $context;

	/** @var array */
	private static $vars = array(
	);

	/** @var array */
	private static $aliases = array(
		'getHttpContext' => 'Nette\\Web\\HttpContext',
		'getHttpRequest' => 'Nette\\Web\\IHttpRequest',
		'getHttpResponse' => 'Nette\\Web\\IHttpResponse',
		'getApplication' => 'Nette\\Application\\Application',
		'getUser' => 'Nette\\Web\\IUser',
		'getRobotLoader' => 'Nette\\Loaders\\RobotLoader',
	);



	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new \LogicException("Cannot instantiate static class " . get_class($this));
	}



	/**
	 * Sets "class behind Environment" configurator.
	 * @param  Nette\DI\Configurator
	 * @return void
	 */
	public static function setConfigurator(DI\Configurator $configurator)
	{
		self::$configurator = $configurator;
	}



	/**
	 * Gets "class behind Environment" configurator.
	 * @return Nette\DI\Configurator
	 */
	public static function getConfigurator()
	{
		if (self::$configurator === NULL) {
			self::$configurator = new DI\Configurator;
		}
		return self::$configurator;
	}



	/********************* environment name and modes ****************d*g**/



	/**
	 * Sets the current environment name.
	 * @param  string
	 * @return void
	 * @throws InvalidStateException
	 */
	public static function setName($name)
	{
		if (!isset(self::$vars['environment'])) {
			self::setVariable('environment', $name, FALSE);

		} else {
			throw new InvalidStateException('Environment name has already been set.');
		}
	}



	/**
	 * Returns the current environment name.
	 * @return string
	 */
	public static function getName()
	{
		$name = self::getVariable('environment', NULL);
		if ($name === NULL) {
			$name = self::getConfigurator()->detect('environment');
			self::setVariable('environment', $name, FALSE);
		}
		return $name;
	}



	/**
	 * Sets the mode.
	 * @param  string mode identifier
	 * @param  bool   set or unset
	 * @return void
	 */
	public static function setMode($mode, $value = TRUE)
	{
		self::$modes[$mode] = (bool) $value;
	}



	/**
	 * Returns the mode.
	 * @param  string mode identifier
	 * @return bool
	 */
	public static function getMode($mode)
	{
		if (isset(self::$modes[$mode])) {
			return self::$modes[$mode];

		} else {
			return self::$modes[$mode] = self::getConfigurator()->detect($mode);
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
	 * Determines whether a server is running in production mode.
	 * @return bool
	 */
	public static function isProduction()
	{
		return self::getMode('production');
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
		if (!is_string($value)) {
			$expand = FALSE;
		}
		self::$vars[$name] = array($value, (bool) $expand);
	}



	/**
	 * Returns the value of an environment variable or $default if there is no element set.
	 * @param  string
	 * @param  mixed  default value to use if key not found
	 * @return mixed
	 * @throws InvalidStateException
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

			} elseif (func_num_args() > 1) {
				return $default;

			} else {
				throw new InvalidStateException("Unknown environment variable '$name'.");
			}
		}
	}



	/**
	 * Returns the all environment variables.
	 * @return array
	 */
	public static function getVariables()
	{
		$res = array();
		foreach (self::$vars as $name => $foo) {
			$res[$name] = self::getVariable($name);
		}
		return $res;
	}



	/**
	 * Returns expanded variable.
	 * @param  string
	 * @return string
	 * @throws InvalidStateException
	 */
	public static function expand($var)
	{
		static $livelock;
		if (is_string($var) && strpos($var, '%') !== FALSE) {
			return @preg_replace_callback(
				'#%([a-z0-9_-]*)%#i',
				function ($m) use (& $livelock) {
					list(, $var) = $m;
					if ($var === '') return '%';

					if (isset($livelock[$var])) {
						throw new InvalidStateException("Circular reference detected for variables: "
							. implode(', ', array_keys($livelock)) . ".");
					}

					try {
						$livelock[$var] = TRUE;
						$val = Environment::getVariable($var);
						unset($livelock[$var]);
					} catch (\Exception $e) {
						$livelock = array();
						throw $e;
					}

					if (!is_scalar($val)) {
						throw new InvalidStateException("Environment variable '$var' is not scalar.");
					}

					return $val;
				},
				$var
			); // intentionally @ due PHP bug #39257
		}
		return $var;
	}



	/********************* context ****************d*g**/



	/**
	 * Get initial instance of context.
	 * @return Nette\DI\IContext
	 */
	public static function getContext()
	{
		if (self::$context === NULL) {
			self::$context = self::getConfigurator()->createContext();
		}
		return self::$context;
	}



	/**
	 * Gets the service object of the specified type.
	 * @param  string service name
	 * @param  array  options in case service is not singleton
	 * @return object
	 */
	public static function getService($name, array $options = NULL)
	{
		return self::getContext()->getService($name, $options);
	}



	/**
	 * Adds new Environment::get<Service>() method.
	 * @param  string  service name
	 * @param  string  alias name
	 * @return void
	 */
	public static function setServiceAlias($service, $alias)
	{
		self::$aliases['get' . ucfirst($alias)] = $service;
	}



	/**
	 * Calling to undefined static method.
	 * @param  string  method name
	 * @param  array   arguments
	 * @return object  service
	 */
	public static function __callStatic($name, $args)
	{
		if (isset(self::$aliases[$name])) {
			return self::getContext()->getService(self::$aliases[$name], $args);
		} else {
			throw new MemberAccessException("Call to undefined static method Nette\\Environment::$name().");
		}
	}



	/**
	 * @return Nette\Http\Request
	 */
	public static function getHttpRequest()
	{
		return self::getContext()->getService(self::$aliases[__FUNCTION__]);
	}



	/**
	 * @return Nette\Http\Context
	 */
	public static function getHttpContext()
	{
		return self::getContext()->getService(self::$aliases[__FUNCTION__]);
	}



	/**
	 * @return Nette\Http\Response
	 */
	public static function getHttpResponse()
	{
		return self::getContext()->getService(self::$aliases[__FUNCTION__]);
	}



	/**
	 * @return Nette\Application\Application
	 */
	public static function getApplication()
	{
		return self::getContext()->getService(self::$aliases[__FUNCTION__]);
	}



	/**
	 * @return Nette\Http\User
	 */
	public static function getUser()
	{
		return self::getContext()->getService(self::$aliases[__FUNCTION__]);
	}



	/**
	 * @return Nette\Loaders\RobotLoader
	 */
	public static function getRobotLoader()
	{
		return self::getContext()->getService(self::$aliases[__FUNCTION__]);
	}



	/********************* service factories ****************d*g**/



	/**
	 * @param  string
	 * @return Nette\Caching\Cache
	 */
	public static function getCache($namespace = '')
	{
		return new Caching\Cache(
			self::getService('Nette\\Caching\\ICacheStorage'),
			$namespace
		);
	}



	/**
	 * Returns instance of session or session namespace.
	 * @param  string
	 * @return Nette\Http\Session
	 */
	public static function getSession($namespace = NULL)
	{
		$handler = self::getService('Nette\\Web\\Session');
		return $namespace === NULL ? $handler : $handler->getNamespace($namespace);
	}



	/********************* global configuration ****************d*g**/



	/**
	 * Loads global configuration from file and process it.
	 * @param  string|Nette\Config\Config  file name or Config object
	 * @return \ArrayObject
	 */
	public static function loadConfig($file = NULL)
	{
		return self::$config = self::getConfigurator()->loadConfig($file);
	}



	/**
	 * Returns the global configuration.
	 * @param  string key
	 * @param  mixed  default value
	 * @return mixed
	 */
	public static function getConfig($key = NULL, $default = NULL)
	{
		if (func_num_args()) {
			return isset(self::$config[$key]) ? self::$config[$key] : $default;

		} else {
			return self::$config;
		}
	}

}
