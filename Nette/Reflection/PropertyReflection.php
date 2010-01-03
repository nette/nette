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
 * Reports information about a classes variable.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette
 */
class PropertyReflection extends /*\*/ReflectionProperty
{

	/**
	 * @return Nette\Reflection\PropertyReflection
	 */
	static function import(/*\*/ReflectionProperty $ref)
	{
		return new self($ref->getDeclaringClass()->getName(), $ref->getName());
	}



	/**
	 * @return Nette\Reflection\ClassReflection
	 */
	function getDeclaringClass()
	{
		return ClassReflection::import(parent::getDeclaringClass());
	}



	function __toString()
	{
		return 'Property ' . parent::getDeclaringClass()->getName() . '::$' . $this->getName();
	}



	/********************* Nette\Annotations support ****************d*g**/



	/**
	 * Has property specified annotation?
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
