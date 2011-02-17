<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Reflection;

use Nette,
	Nette\ObjectMixin;



/**
 * Reports information about a method.
 *
 * @author     David Grudl
 */
class MethodReflection extends \ReflectionMethod
{

	/**
	 * @param  string|object
	 * @param  string
	 * @return Nette\Reflection\MethodReflection
	 */
	public static function from($class, $method)
	{
		return new static(is_object($class) ? get_class($class) : $class, $method);
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
	public function invokeNamedArgs($object, $args, $needAllArgs = false)
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
        if($needAllArgs && !array_key_exists($name, $args)) {
          throw new \InvalidArgumentException("Missing argument '$name'");
        }
				else {
          $res[$i++] = $def;
        }
			}
		}
		return $this->invokeArgs($object, $res);
	}



	/**
	 * @return Nette\Callback
	 */
	public function getCallback()
	{
		return new Nette\Callback(parent::getDeclaringClass()->getName(), $this->getName());
	}



	public function __toString()
	{
		return 'Method ' . parent::getDeclaringClass()->getName() . '::' . $this->getName() . '()';
	}



	/********************* Reflection layer ****************d*g**/



	/**
	 * @return Nette\Reflection\ClassReflection
	 */
	public function getDeclaringClass()
	{
		return new ClassReflection(parent::getDeclaringClass()->getName());
	}



	/**
	 * @return Nette\Reflection\MethodReflection
	 */
	public function getPrototype()
	{
		$prototype = parent::getPrototype();
		return new MethodReflection($prototype->getDeclaringClass()->getName(), $prototype->getName());
	}



	/**
	 * @return Nette\Reflection\ExtensionReflection
	 */
	public function getExtension()
	{
		return ($name = $this->getExtensionName()) ? new ExtensionReflection($name) : NULL;
	}



	public function getParameters()
	{
		$me = array(parent::getDeclaringClass()->getName(), $this->getName());
		foreach ($res = parent::getParameters() as $key => $val) {
			$res[$key] = new ParameterReflection($me, $val->getName());
		}
		return $res;
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
	public /**/static/**/ function getReflection()
	{
		return new Nette\Reflection\ClassReflection(/*5.2*$this*//**/get_called_class()/**/);
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
		throw new \MemberAccessException("Cannot unset the property {$this->reflection->name}::\$$name.");
	}

}
