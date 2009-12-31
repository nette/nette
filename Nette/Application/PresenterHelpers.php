<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Application
 */

/*namespace Nette\Application;*/



/**
 * Helpers for Presenter & PresenterComponent.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
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

	/** @var array getDefaultParameters cache */
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
		if ($cache === NULL) try {
			$cache = FALSE;
			$rm = /*Nette\Reflection\*/MethodReflection::create($class, $method);
			$cache = $rm->isCallable() && !$rm->isStatic();
		} catch (/*\*/ReflectionException $e) {
		}
		return $cache;
	}



	/**
	 * Converts list of arguments to named parameters.
	 * Used by Presenter::createRequest()
	 * @param  string  class name
	 * @param  string  method name
	 * @param  array   arguments
	 * @param  array   supplemental arguments
	 * @return void
	 * @throws InvalidLinkException
	 */
	public static function argsToParams($class, $method, & $args, $supplemental = array())
	{
		$params = & self::$mpCache[strtolower($class . ':' . $method)];
		if ($params === NULL) {
			$params = /*Nette\Reflection\*/MethodReflection::create($class, $method)->getDefaultParameters();
		}
		$i = 0;
		foreach ($params as $name => $def) {
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

}
