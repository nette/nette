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



/**
 * Reports information about a method.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette
 */
class MethodReflection extends /*\*/ReflectionMethod
{

	/**
	 * @return Nette\Reflection\MethodReflection
	 */
	static function import(/*\*/ReflectionMethod $ref)
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



	/**
	 * @return Nette\Reflection\ExtensionReflection
	 */
	function getExtension()
	{
		return ($ref = parent::getExtension()) ? ExtensionReflection::import($ref) : NULL;
	}



	function getParameters()
	{
		return array_map(/*Nette\Reflection\*/'MethodParameterReflection::import', parent::getParameters());
	}



	function __toString()
	{
		return 'Method ' . parent::getDeclaringClass()->getName() . '::' . $this->getName() . '()';
	}



	/********************* Nette\Annotations support ****************d*g**/



	/**
	 * Has method specified annotation?
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
 * Reports information about a function.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette
 */
class FunctionReflection extends /*\*/ReflectionFunction
{

	/**
	 * @return Nette\Reflection\FunctionReflection
	 */
	static function import(/*\*/ReflectionFunction $ref)
	{
		return new self($ref->getName());
	}



	/**
	 * @return Nette\Reflection\ExtensionReflection
	 */
	function getExtension()
	{
		return ($ref = parent::getExtension()) ? ExtensionReflection::import($ref) : NULL;
	}



	function getParameters()
	{
		return array_map(/*Nette\Reflection\*/'MethodParameterReflection::import', parent::getParameters());
	}



	function __toString()
	{
		return 'Function ' . $this->getName() . '()';
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
 * Reports information about a method's parameter.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette
 */
class MethodParameterReflection extends /*\*/ReflectionParameter
{

	/**
	 * @return Nette\Reflection\MethodParameterReflection
	 */
	static function import(/*\*/ReflectionParameter $ref)
	{
		$method = $ref->getDeclaringFunction();
		return new self($method instanceof /*\*/ReflectionMethod ? array($ref->getDeclaringClass()->getName(), $method->getName()) : $method->getName(), $ref->getName());
	}



	/**
	 * @return Nette\Reflection\ClassReflection
	 */
	function getClass()
	{
		return ($ref = parent::getClass()) ? ClassReflection::import($ref) : NULL;
	}



	/**
	 * @return Nette\Reflection\ClassReflection
	 */
	function getDeclaringClass()
	{
		return ($ref = parent::getDeclaringClass()) ? ClassReflection::import($ref) : NULL;
	}



	/**
	 * @return Nette\Reflection\MethodReflection | Nette\Reflection\FunctionReflection
	 */
	function getDeclaringFunction()
	{
		return ($ref = parent::getDeclaringFunction()) instanceof /*\*/ReflectionMethod
			? MethodReflection::import($ref)
			: FunctionReflection::import($ref);
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
 * Reports information about a extension.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette
 */
class ExtensionReflection extends /*\*/ReflectionExtension
{

	/**
	 * @return Nette\Reflection\ExtensionReflection
	 */
	static function import(/*\*/ReflectionExtension $ref)
	{
		return new self($ref->getName());
	}



	function getClasses()
	{
		return array_map(/*Nette\Reflection\*/'ClassReflection::import', parent::getClasses());
	}



	function getFunctions()
	{
		return array_map(/*Nette\Reflection\*/'FunctionReflection::import', parent::getFunctions());
	}



	function __toString()
	{
		return 'Extension ' . $this->getName();
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
