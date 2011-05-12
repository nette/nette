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

	/** @var Nette\Configurator */
	private static $configurator;

	/** @var Nette\DI\IContainer */
	private static $context;

	/** @var array */
	private static $vars = array(
	);



	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new StaticClassException;
	}



	/**
	 * Sets "class behind Environment" configurator.
	 * @param  Nette\Configurator
	 * @return void
	 */
	public static function setConfigurator(Configurator $configurator)
	{
		self::$configurator = $configurator;
	}



	/**
	 * Gets "class behind Environment" configurator.
	 * @return Nette\Configurator
	 */
	public static function getConfigurator()
	{
		if (self::$configurator === NULL) {
			self::$configurator = new Configurator;
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
		self::getContext()->params[$mode . 'Mode'] = (bool) $value;
	}



	/**
	 * Returns the mode.
	 * @param  string mode identifier
	 * @return bool
	 */
	public static function getMode($mode)
	{
		if (isset(self::getContext()->params[$mode . 'Mode'])) {
			return self::getContext()->params[$mode . 'Mode'];

		} else {
			return self::getContext()->params[$mode . 'Mode'] = self::getConfigurator()->detect($mode);
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
		$expand = $expand && is_string($value) && strpos($value, '%') !== FALSE;
		if (!$expand) {
			self::getContext()->params[$name] = $value;
		}
		self::$vars[$name] = array($value, $expand);
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
			return @preg_replace_callback( // intentionally @
				'#%([a-z0-9_-]*)%#i',
				function ($m) use (& $livelock) {
					list(, $var) = $m;
					if ($var === '') {
						return '%';
					}

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
	 * @return Nette\DI\IContainer
	 */
	public static function getContext()
	{
		if (self::$context === NULL) {
			self::$context = self::getConfigurator()->createContainer();
		}
		return self::$context;
	}



	/**
	 * Gets the service object of the specified type.
	 * @param  string service name
	 * @return object
	 */
	public static function getService($name)
	{
		return self::getContext()->getService($name);
	}



	/**
	 * Calling to undefined static method.
	 * @param  string  method name
	 * @param  array   arguments
	 * @return object  service
	 */
	public static function __callStatic($name, $args)
	{
		if (!$args && strncasecmp($name, 'get', 3) === 0) {
			return self::getContext()->getService(lcfirst(substr($name, 3)));
		} else {
			throw new MemberAccessException("Call to undefined static method Nette\\Environment::$name().");
		}
	}



	/**
	 * @return Nette\Http\Request
	 */
	public static function getHttpRequest()
	{
		return self::getContext()->httpRequest;
	}



	/**
	 * @return Nette\Http\Context
	 */
	public static function getHttpContext()
	{
		return self::getContext()->httpContext;
	}



	/**
	 * @return Nette\Http\Response
	 */
	public static function getHttpResponse()
	{
		return self::getContext()->httpResponse;
	}



	/**
	 * @return Nette\Application\Application
	 */
	public static function getApplication()
	{
		return self::getContext()->application;
	}



	/**
	 * @return Nette\Http\User
	 */
	public static function getUser()
	{
		return self::getContext()->user;
	}



	/**
	 * @return Nette\Loaders\RobotLoader
	 */
	public static function getRobotLoader()
	{
		return self::getContext()->robotLoader;
	}



	/********************* service factories ****************d*g**/



	/**
	 * @param  string
	 * @return Nette\Caching\Cache
	 */
	public static function getCache($namespace = '')
	{
		return new Caching\Cache(self::getContext()->cacheStorage, $namespace);
	}



	/**
	 * Returns instance of session or session namespace.
	 * @param  string
	 * @return Nette\Http\Session
	 */
	public static function getSession($namespace = NULL)
	{
		return $namespace === NULL
			? self::getContext()->session
			: self::getContext()->session->getNamespace($namespace);
	}



	/********************* global configuration ****************d*g**/



	/**
	 * Loads global configuration from file and process it.
	 * @param  string  file name
	 * @return \ArrayObject
	 */
	public static function loadConfig($file = NULL)
	{
		return self::getConfigurator()->loadConfig(self::getContext(), $file);
	}



	/**
	 * Returns the global configuration.
	 * @param  string key
	 * @param  mixed  default value
	 * @return mixed
	 */
	public static function getConfig($key = NULL, $default = NULL)
	{
		$config = self::getContext()->config;
		if (func_num_args()) {
			return isset($config[$key]) ? $config[$key] : $default;

		} else {
			return $config;
		}
	}

}
