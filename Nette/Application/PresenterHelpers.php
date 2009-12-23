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
 * @package    Nette\Application
 */

/*namespace Nette\Application;*/



/**
 * Helpers for Presenter & PresenterComponent.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Application
 * @internal
 */
final class PresenterHelpers
{
	/** @var array getPersistentParams cache */
	private static $ppCache = array();

	/** @var array getPersistentComponents cache */
	private static $pcCache = array();

	/** @var array isMethodCallable cache */
	private static $mcCache = array();

	/** @var array getMethodParams cache */
	private static $mpCache = array();



	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new /*\*/LogicException("Cannot instantiate static class " . get_class($this));
	}



	/**
	 * Returns array of classes persistent parameters.
	 * @param  string  class name
	 * @return array
	 */
	public static function getPersistentParams($class)
	{
		$params = & self::$ppCache[$class];
		if ($params !== NULL) return $params;
		$params = array();
		if (is_subclass_of($class, /*Nette\Application\*/'PresenterComponent')) {
			// $class::getPersistentParams() in PHP 5.3
			$defaults = get_class_vars($class);
			foreach (call_user_func(array($class, 'getPersistentParams'), $class) as $name => $meta) {
				if (is_string($meta)) $name = $meta;
				$params[$name] = array(
					'def' => $defaults[$name],
					'since' => $class,
				);
			}
			$params = self::getPersistentParams(get_parent_class($class)) + $params;
		}
		return $params;
	}



	/**
	 * Returns array of classes persistent components.
	 * @param  string  class name
	 * @return array
	 */
	public static function getPersistentComponents($class)
	{
		$components = & self::$pcCache[$class];
		if ($components !== NULL) return $components;
		$components = array();
		if (is_subclass_of($class, /*Nette\Application\*/'Presenter')) {
			// $class::getPersistentComponents() in PHP 5.3
			foreach (call_user_func(array($class, 'getPersistentComponents'), $class) as $name => $meta) {
				if (is_string($meta)) $name = $meta;
				$components[$name] = array('since' => $class);
			}
			$components = self::getPersistentComponents(get_parent_class($class)) + $components;
		}
		return $components;
	}



	/**
	 * Is a method callable? It means class is instantiable and method has
	 * public visibility, is non-static and non-abstract.
	 * @param  string  class name
	 * @param  string  method name
	 * @return bool
	 */
	public static function isMethodCallable($class, $method)
	{
		$cache = & self::$mcCache[strtolower($class . ':' . $method)];
		if ($cache !== NULL) return $cache;

		try {
			$cache = FALSE;
			// check class
			$rc = new /*\*/ReflectionClass($class);
			if (!$rc->isInstantiable()) {
				return FALSE;
			}

			// check method
			$rm = $rc->getMethod($method);
			if (!$rm || !$rm->isPublic() || $rm->isAbstract() || $rm->isStatic()) {
				return FALSE;
			}

			return $cache = TRUE;

		} catch (/*\*/ReflectionException $e) {
			return FALSE;
		}
	}



	/**
	 * Converts named parameters to list of arguments.
	 * Used by PresenterComponent::tryCall()
	 * @param  string  class name
	 * @param  string  method name
	 * @param  array   parameters - associative array
	 * @return array   arguments  - list
	 */
	public static function paramsToArgs($class, $method, $params)
	{
		$args = array();
		$i = 0;
		foreach (self::getMethodParams($class, $method) as $name => $def) {
			if (isset($params[$name])) { // NULL treats as none value
				$val = $params[$name];
				if ($def !== NULL) {
					settype($val, gettype($def));
				}
				$args[$i++] = $val;
			} else {
				$args[$i++] = $def;
			}
		}

		return $args;
	}



	/**
	 * Converts list of arguments to named parameters.
	 * Used by Presenter::createRequest() & PresenterComponent::link()
	 * @param  string  class name
	 * @param  string  method name
	 * @param  array   arguments
	 * @param  array   supplemental arguments
	 * @return void
	 * @throws InvalidLinkException
	 */
	public static function argsToParams($class, $method, & $args, $supplemental = array())
	{
		$i = 0;
		foreach (self::getMethodParams($class, $method) as $name => $def) {
			if (array_key_exists($i, $args)) {
				$args[$name] = $args[$i];
				unset($args[$i]);
				$i++;

			} elseif (array_key_exists($name, $args)) {
				// continue with process

			} elseif (array_key_exists($name, $supplemental)) {
				$args[$name] = $supplemental[$name];

			} else {
				continue;
			}

			if ($def === NULL) {
				if ((string) $args[$name] === '') $args[$name] = NULL; // value transmit is unnecessary
			} else {
				settype($args[$name], gettype($def));
				if ($args[$name] === $def) $args[$name] = NULL;
			}
		}

		if (array_key_exists($i, $args)) {
			throw new InvalidLinkException("Extra parameter for signal '$class:$method'.");
		}
	}



	/**
	 * Returns array of methods parameters and theirs default values.
	 * @param  string  class name
	 * @param  string  method name
	 * @return array
	 */
	private static function getMethodParams($class, $method)
	{
		$cache = & self::$mpCache[strtolower($class . ':' . $method)];
		if ($cache !== NULL) return $cache;
		$rm = new /*\*/ReflectionMethod($class, $method);
		$cache = array();
		foreach ($rm->getParameters() as $param) {
			$cache[$param->getName()] = $param->isDefaultValueAvailable()
				? $param->getDefaultValue()
				: NULL;

			if ($param->isArray()) {
				settype($cache[$param->getName()], 'array');
			}
		}
		return $cache;
	}

}
