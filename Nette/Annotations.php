<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette
 */

/*namespace Nette;*/

/*use Nette\Reflection\AnnotationsParser;*/



/**
 * Annotations support for PHP.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette
 * @deprecated
 */
final class Annotations
{
	/** @var bool */
	public static $useReflection;



	/**
	 * Has class/method/property specified annotation?
	 * @param  \ReflectionClass|\ReflectionMethod|\ReflectionProperty
	 * @param  string    annotation name
	 * @return bool
	 */
	public static function has(/*\*/Reflector $r, $name)
	{
		$cache = AnnotationsParser::getAll($r);
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
		$cache = AnnotationsParser::getAll($r);
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
		$cache = AnnotationsParser::getAll($r);

		if ($name === NULL) {
			return $cache;

		} elseif (isset($cache[$name])) {
			return $cache[$name];

		} else {
			return array();
		}
	}

}
