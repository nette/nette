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
 * @package    Nette::Application
 */

/*namespace Nette::Application;*/



/**
 * Helpers for Presenter & PresenterComponent.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Application
 * @version    $Revision$ $Date$
 */
class PresenterHelpers
{
	/** @var array getPersistentParams cache */
	private static $ppCache = array();

	/** @var array isMethodCallable cache */
	private static $mcCache = array();

	/** @var array getMethodParams cache */
	private static $mpCache = array();



	/**
	 * Returns array of classes persistent parameters. Class must implements IStatePersistent.
     * Persistent parameter has annotation @persistent, public visibility and is non-static.
	 * @param  string  class name
	 * @return array
	 */
	final static public function getPersistentParams($class)
	{
		$meta = & self::$ppCache[strtolower($class)];
		if ($meta !== NULL) return $meta;

		try {
			$meta = array();
			$rc = new ReflectionClass($class);
			if (!$rc->implementsInterface(/*Nette::Application::*/'IStatePersistent')) return array();

			// generate
			foreach ($rc->getDefaultProperties() as $nm => $val)
			{
				$rp = $rc->getProperty($nm);
				if (!$rp->isPublic() || $rp->isStatic()) continue;

				if (!strpos($rp->getDocComment(), '@persistent')) continue;

				$decl = $rp->getDeclaringClass();
				// find REAL declaring class
				while (($tmp = $decl->getParentClass()) && $tmp->hasProperty($nm) && $tmp->getProperty($nm)->isPublic()) {
					$decl = $tmp;
				}

				$meta[$nm] = array(
					'def' => $val, // default value from $class
					'type' => $val === NULL ? NULL : gettype($val), // forced type
					'since' => $decl->getName(),
				);
			}
		} catch (ReflectionException $e) {
		}
		return $meta;
	}



	/**
	 * Is a method callable? It means class is instantiable and method has
	 * public visibility, is non-static and non-abstract.
	 * @param  string  class name
	 * @param  string  method name
	 * @return bool
	 */
	final static public function isMethodCallable($class, $method)
	{
		return self::getMethodAnnotations($class, $method) !== FALSE;
	}



	/**
	 * Returns array of annotations for "callable" methods. @see isMethodCallable()
	 * @param  string  class name
	 * @param  string  method name
	 * @return array
	 */
	final static public function getMethodAnnotations($class, $method)
	{
		$cache = & self::$mcCache[strtolower($class . ':' . $method)];
		if ($cache !== NULL) return $cache;

		try {
			$cache = FALSE;
			// check class
			$rc = new ReflectionClass($class);
			if (!$rc->isInstantiable()) {
				return FALSE;
			}

			// check method
			$rm = $rc->getMethod($method);
			if (!$rm || !$rm->isPublic() || $rm->isAbstract() || $rm->isStatic()) {
				return FALSE;
			}

			// parse annotation
			if (preg_match_all('#@([a-z]+):\s*(\S+)#', $rm->getDocComment(), $match)) {
				$cache = array_combine($match[1], $match[2]);
			} else {
				$cache = array();
			}
			return $cache;

		} catch (ReflectionException $e) {
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
	final static public function paramsToArgs($class, $method, $params)
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
	 * @return void
	 */
	final static public function argsToParams($class, $method, & $args)
	{
		$i = 0;
		foreach (self::getMethodParams($class, $method) as $name => $def) {
			if (array_key_exists($i, $args)) {
				$args[$name] = $args[$i];
				unset($args[$i]);
			} elseif (!array_key_exists($name, $args)) {
				continue;
			}
			if ($args[$name] == $def) $args[$name] = NULL;
			$i++;
		}

		if (array_key_exists($i, $args)) {
			trigger_error("Extra parameters for '$class:$method'", E_USER_WARNING);
		}
	}



	/**
	 * Returns array of methods parameters and theirs default values.
	 * @param  string  class name
	 * @param  string  method name
	 * @return array
	 */
	final static public function getMethodParams($class, $method)
	{
		$cache = & self::$mpCache[strtolower($class . ':' . $method)];
		if ($cache !== NULL) return $cache;
		$rm = new ReflectionMethod($class, $method);
		$cache = array();
		foreach ($rm->getParameters() as $param) {
			$cache[$param->getName()] = $param->isDefaultValueAvailable()
				? $param->getDefaultValue()
				: NULL;
		}
		return $cache;
	}

}
