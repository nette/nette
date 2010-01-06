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
	public static function create($class, $method)
	{
		return new self(is_object($class) ? get_class($class) : $class, $method);
	}



	/**
	 * @return array
	 */
	public function getDefaultParameters()
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
	 * Invokes method using named parameters.
	 * @param  object
	 * @param  array
	 * @return mixed
	 */
	public function invokeNamedArgs($object, $args)
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



	public function __toString()
	{
		return 'Method ' . parent::getDeclaringClass()->getName() . '::' . $this->getName() . '()';
	}



	/********************* Reflection layer ****************d*g**/



	/**
	 * @return Nette\Reflection\MethodReflection
	 */
	public static function import(/*\*/ReflectionMethod $ref)
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



	/**
	 * @return Nette\Reflection\ExtensionReflection
	 */
	public function getExtension()
	{
		return ($ref = parent::getExtension()) ? ExtensionReflection::import($ref) : NULL;
	}



	public function getParameters()
	{
		return array_map(/*Nette\Reflection\*/'MethodParameterReflection::import', parent::getParameters());
	}



	/********************* Nette\Annotations support ****************d*g**/



	/**
	 * Has method specified annotation?
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
