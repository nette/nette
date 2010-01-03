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
 * Reports information about a method.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette
 */
class MethodReflection extends /*\*/ReflectionMethod
{

	/**
	 * @param  string|object
	 * @param  string
	 * @return Nette\Reflection\MethodReflection
	 */
	static function create($class, $method)
	{
		return new self(is_object($class) ? get_class($class) : $class, $method);
	}



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



	/**
	 * @return array
	 */
	function getDefaultParameters()
	{
		$res = array();
		foreach (parent::getParameters() as $param) {
			$res[$param->getName()] = $param->isDefaultValueAvailable()
				? $param->getDefaultValue()
				: NULL;

			if ($param->isArray()) {
				settype($res[$param->getName()], 'array');
			}
		}
		return $res;
	}



	/**
	 * Is a method callable? (class is instantiable, method is public and non-abstract).
	 * @return bool
	 */
	function isCallable()
	{
		return parent::getDeclaringClass()->isInstantiable() && $this->isPublic() && !$this->isAbstract();
	}



	/**
	 * Invokes method using named parameters.
	 * @param  object
	 * @param  array
	 * @return mixed
	 */
	function invokeNamedArgs($object, $args)
	{
		$res = array();
		$i = 0;
		foreach ($this->getDefaultParameters() as $name => $def) {
			if (isset($args[$name])) { // NULL treats as none value
				$val = $args[$name];
				if ($def !== NULL) {
					settype($val, gettype($def));
				}
				$res[$i++] = $val;
			} else {
				$res[$i++] = $def;
			}
		}
		return $this->invokeArgs($object, $res);
	}



	/********************* Nette\Annotations support ****************d*g**/



	/**
	 * Has method specified annotation?
	 * @param  string
	 * @return bool
	 */
	function hasAnnotation($name)
	{
		$res = AnnotationsParser::getAll($this);
		return !empty($res[$name]);
	}



	/**
	 * Returns an annotation value.
	 * @param  string
	 * @return IAnnotation
	 */
	function getAnnotation($name)
	{
		$res = AnnotationsParser::getAll($this);
		return isset($res[$name]) ? end($res[$name]) : NULL;
	}



	/**
	 * Returns all annotations.
	 * @return array
	 */
	function getAnnotations()
	{
		return AnnotationsParser::getAll($this);
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
