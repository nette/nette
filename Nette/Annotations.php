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
 * @package    Nette
 * @version    $Id$
 */

/*namespace Nette;*/



/**
 * Annotations support for PHP.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette
 */
final class Annotations
{
	/** @var array */
	static private $cache = array();



	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new /*\*/LogicException("Cannot instantiate static class " . get_class($this));
	}



	/**
	 * Has class/method/property specified annotation?
	 * @param  \ReflectionClass|\ReflectionMethod|\ReflectionProperty
	 * @param  string    annotation name
	 * @return bool
	 */
	public static function has(/*\*/Reflector $r, $name)
	{
		$cache = & self::init($r);
		return !empty($cache[$name]);
	}



	/**
	 * Returns an annotation value.
	 * @param  \ReflectionClass|\ReflectionMethod|\ReflectionProperty
	 * @param  string    annotation name
	 * @return array
	 */
	public static function get(/*\*/Reflector $r, $name)
	{
		$cache = & self::init($r);
		return isset($cache[$name]) ? end($cache[$name]) : NULL;
	}



	/**
	 * Returns all annotations.
	 * @param  \ReflectionClass|\ReflectionMethod|\ReflectionProperty
	 * @param  string    annotation name
	 * @return array
	 */
	public static function getAll(/*\*/Reflector $r, $name = NULL)
	{
		$cache = & self::init($r);

		if ($name === NULL) {
			return $cache;

		} elseif (isset($cache[$name])) {
			return $cache[$name];

		} else {
			return array();
		}
	}



	/**
	 * Parses and caches annotations.
	 * @param  string    phpDoc comment
	 * @return array
	 */
	public static function & init($r)
	{
		$cache = & self::$cache[$r->getName()][$r instanceof /*\*/ReflectionClass ? '' : $r->getDeclaringClass()->getName()];
		if ($cache !== NULL) {
			return $cache;
		}

		preg_match_all('#@([a-zA-Z0-9_]+)(?:\(((?>[^\'")]+|\'[^\']*\'|"[^"]*")*)\))?#', $r->getDocComment(), $matches, PREG_SET_ORDER);
		$cache = array();
		foreach ($matches as $match)
		{
			if (isset($match[2])) {
				preg_match_all('#[,\s](?>([a-zA-Z0-9_]+)\s*=\s*)?([^\'",\s][^,]*|\'[^\']*\'|"[^"]*")#', ',' . $match[2], $matches, PREG_SET_ORDER);
				$items = array();
				$val = TRUE;
				foreach ($matches as $m) {
					list(, $key, $val) = $m;
					if ($val[0] === "'" || $val[0] === '"') {
						$val = substr($val, 1, -1);

					} elseif (strcasecmp($val, 'true') === 0) {
						$val = TRUE;

					} elseif (strcasecmp($val, 'false') === 0) {
						$val = FALSE;

					} elseif (is_numeric($val)) {
						$val = 1 * $val;
					}

					if ($key === '') {
						$items[] = $val;

					} else {
						$items[$key] = $val;
					}
				}

				$items = count($items) < 2 ? $val : new /*\*/ArrayObject($items, /*\*/ArrayObject::ARRAY_AS_PROPS);

			} else {
				$items = TRUE;
			}

			$cache[$match[1]][] = $items;
		}
		return $cache;
	}

}
