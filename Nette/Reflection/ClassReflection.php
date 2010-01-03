<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Reflection
 */

/*namespace Nette\Reflection;*/

/*use Nette\ObjectMixin;*/
/*use Nette\Annotations;*/



/**
 * Reports information about a class.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette
 */
class ClassReflection extends /*\*/ReflectionClass
{

	/**
	 * @param  string|object
	 * @return Nette\Reflection\ClassReflection
	 */
	static function create($class)
	{
		return new self(is_object($class) ? get_class($class) : $class);
	}



	/**
	 * @return Nette\Reflection\ClassReflection
	 */
	static function import(/*\*/ReflectionClass $ref)
	{
		return new self($ref->getName());
	}



	/**
	 * @return Nette\Reflection\MethodReflection
	 */
	function getConstructor()
	{
		return ($ref = parent::getConstructor()) ? MethodReflection::import($ref) : NULL;
	}



	/**
	 * @return Nette\Reflection\ExtensionReflection
	 */
	function getExtension()
	{
		return ($ref = parent::getExtension()) ? ExtensionReflection::import($ref) : NULL;
	}



	function getInterfaces()
	{
		return array_map(/*Nette\Reflection\*/'ClassReflection::import', parent::getInterfaces());
	}



	/**
	 * @return Nette\Reflection\MethodReflection
	 */
	function getMethod($name)
	{
		return MethodReflection::import(parent::getMethod($name));
	}



	function getMethods($filter = -1)
	{
		return array_map(/*Nette\Reflection\*/'MethodReflection::import', parent::getMethods($filter));
	}



	/**
	 * @return Nette\Reflection\ClassReflection
	 */
	function getParentClass()
	{
		return ($ref = parent::getParentClass()) ? self::import($ref) : NULL;
	}



	function getProperties($filter = -1)
	{
		return array_map(/*Nette\Reflection\*/'PropertyReflection::import', parent::getProperties($filter));
	}



	/**
	 * @return Nette\Reflection\PropertyReflection
	 */
	function getProperty($name)
	{
		return PropertyReflection::import(parent::getProperty($name));
	}



	function __toString()
	{
		return 'Class ' . $this->getName();
	}



	/********************* Nette\Annotations support ****************d*g**/



	/**
	 * Has class specified annotation?
	 * @param  string
	 * @return bool
	 */
	function hasAnnotation($name)
	{
		return Annotations::has($this, $name);
	}



	/**
	 * Returns an annotation value.
	 * @param  string
	 * @return array
	 */
	function getAnnotation($name)
	{
		return Annotations::get($this, $name);
	}



	/**
	 * Returns all annotations.
	 * @param  string
	 * @return array
	 */
	function getAnnotations($name = NULL)
	{
		return Annotations::getAll($this, $name);
	}



	/********************* Nette\Object behaviour ****************d*g**/



	/**
	 * @return Nette\Reflection\ObjectReflection
	 */
	function getReflection()
	{
		return new ObjectReflection($this);
	}



	function __call($name, $args)
	{
		return ObjectMixin::call($this, $name, $args);
	}



	function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}



	function __set($name, $value)
	{
		return ObjectMixin::set($this, $name, $value);
	}



	function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}



	function __unset($name)
	{
		throw new /*\*/MemberAccessException("Cannot unset the property {$this->reflection->name}::\$$name.");
	}

}



/**
 * Reports information about a object.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette
 */
class ObjectReflection extends ClassReflection
{

	function __construct($obj)
	{
		parent::__construct(get_class($obj));
	}

}
