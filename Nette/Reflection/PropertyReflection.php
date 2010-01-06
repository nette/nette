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
 * Reports information about a classes variable.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette
 */
class PropertyReflection extends /*\*/ReflectionProperty
{

	public function __toString()
	{
		return 'Property ' . parent::getDeclaringClass()->getName() . '::$' . $this->getName();
	}



	/********************* Reflection layer ****************d*g**/



	/**
	 * @return Nette\Reflection\PropertyReflection
	 */
	public static function import(/*\*/ReflectionProperty $ref)
	{
		return new self($ref->getDeclaringClass()->getName(), $ref->getName());
	}



	/**
	 * @return Nette\Reflection\ClassReflection
	 */
	public function getDeclaringClass()
	{
		return ClassReflection::import(parent::getDeclaringClass());
	}



	/********************* Nette\Annotations support ****************d*g**/



	/**
	 * Has property specified annotation?
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
	public /*static */function getReflection()
	{
		return new /*Nette\Reflection\*/ClassReflection(/**/$this/**//*get_called_class()*/);
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
