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
	public static function create($class)
	{
		return new self($class);
	}



	public function __toString()
	{
		return 'Class ' . $this->getName();
	}



	/********************* Reflection layer ****************d*g**/



	/**
	 * @return Nette\Reflection\ClassReflection
	 */
	public static function import(/*\*/ReflectionClass $ref)
	{
		return new self($ref->getName());
	}



	/**
	 * @return Nette\Reflection\MethodReflection
	 */
	public function getConstructor()
	{
		return ($ref = parent::getConstructor()) ? MethodReflection::import($ref) : NULL;
	}



	/**
	 * @return Nette\Reflection\ExtensionReflection
	 */
	public function getExtension()
	{
		return ($ref = parent::getExtension()) ? ExtensionReflection::import($ref) : NULL;
	}



	public function getInterfaces()
	{
		return array_map(/*Nette\Reflection\*/'ClassReflection::import', parent::getInterfaces());
	}



	/**
	 * @return Nette\Reflection\MethodReflection
	 */
	public function getMethod($name)
	{
		return MethodReflection::import(parent::getMethod($name));
	}



	public function getMethods($filter = -1)
	{
		return array_map(/*Nette\Reflection\*/'MethodReflection::import', parent::getMethods($filter));
	}



	/**
	 * @return Nette\Reflection\ClassReflection
	 */
	public function getParentClass()
	{
		return ($ref = parent::getParentClass()) ? self::import($ref) : NULL;
	}



	public function getProperties($filter = -1)
	{
		return array_map(/*Nette\Reflection\*/'PropertyReflection::import', parent::getProperties($filter));
	}



	/**
	 * @return Nette\Reflection\PropertyReflection
	 */
	public function getProperty($name)
	{
		return PropertyReflection::import(parent::getProperty($name));
	}



	/********************* Nette\Annotations support ****************d*g**/



	/**
	 * Has class specified annotation?
	 * @param  string
	 * @return bool
	 */
	public function hasAnnotation($name)
	{
		$res = AnnotationsParser::getAll($this);
		return !empty($res[$name]);
	}



	/**
	 * Returns an annotation value.
	 * @param  string
	 * @return IAnnotation
	 */
	public function getAnnotation($name)
	{
		$res = AnnotationsParser::getAll($this);
		return isset($res[$name]) ? end($res[$name]) : NULL;
	}



	/**
	 * Returns all annotations.
	 * @return array
	 */
	public function getAnnotations()
	{
		return AnnotationsParser::getAll($this);
	}



	/********************* Nette\Object behaviour ****************d*g**/



	/**
	 * @return Nette\Reflection\ClassReflection
	 */
	public function getReflection()
	{
		return new ClassReflection($this);
	}



	public function __call($name, $args)
	{
		return ObjectMixin::call($this, $name, $args);
	}



	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}



	public function __set($name, $value)
	{
		return ObjectMixin::set($this, $name, $value);
	}



	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}



	public function __unset($name)
	{
		throw new /*\*/MemberAccessException("Cannot unset the property {$this->reflection->name}::\$$name.");
	}

}
