<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Application
 * @version    $Id$
 */

/*namespace Nette\Application;*/



/**
 * Helpers for Presenter & PresenterComponent.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette\Application
 * @internal
 */
class PresenterHelpers
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
			if (!$rc->implementsInterface(/*Nette\Application\*/'IStatePersistent')) return array();

			$sinces = $rc->isSubclassOf(/*Nette\Application\*/'Presenter');

			// generate
			foreach ($rc->getDefaultProperties() as $nm => $val)
			{
				$rp = $rc->getProperty($nm);
				if (!$rp->isPublic() || $rp->isStatic() || !Annotations::get($rp, 'persistent')) continue;

				$meta[$nm] = array(
					'def' => $val, // default value from $class
					'type' => $val === NULL ? NULL : gettype($val), // forced type
				);

				if ($sinces) {
					$decl = $rp->getDeclaringClass();
					// find REAL declaring class
					while (($tmp = $decl->getParentClass()) && $tmp->hasProperty($nm) && $tmp->getProperty($nm)->isPublic()) {
						$decl = $tmp;
					}
					$meta[$nm]['since'] = $decl->getName();
				}

			}
		} catch (ReflectionException $e) {
		}
		return $meta;
	}



	/**
	 * Returns array of classes persistent components.
	 * Persistent components has class-level annotation @persistent(cmp1, cmp2).
	 * @param  string  class name
	 * @return array
	 */
	final static public function getPersistentComponents($class)
	{
		$meta = & self::$pcCache[strtolower($class)];
		if ($meta !== NULL) return $meta;

		try {
			$meta = array();
			$rc = new ReflectionClass($class);
			if ($rc->isSubclassOf(/*Nette\Application\*/'Presenter')) {
				$meta = array_fill_keys((array) Annotations::get($rc, 'persistent'), $class)
					+ self::getPersistentComponents(get_parent_class($class));
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

			return $cache = TRUE;

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
	 * @param  array   supplemental arguments
	 * @return void
	 * @throws InvalidLinkException
	 */
	final static public function argsToParams($class, $method, & $args, $supplemental = array())
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
