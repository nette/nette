<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Reflection;

use Nette,
	Nette\ObjectMixin;



/**
 * Reports information about a method's parameter.
 *
 * @author     David Grudl
 * @property-read ClassType $class
 * @property-read string $className
 * @property-read ClassType $declaringClass
 * @property-read Method $declaringFunction
 * @property-read string $name
 * @property-read string $annotation
 * @property-read bool $passedByReference
 * @property-read bool $array
 * @property-read int $position
 * @property-read bool $optional
 * @property-read bool $defaultValueAvailable
 * @property-read mixed $defaultValue
 */
class Parameter extends \ReflectionParameter
{
	/** @var mixed */
	private $function;


	public function __construct($function, $parameter)
	{
		parent::__construct($this->function = $function, $parameter);
	}



	/**
	 * @return ClassType
	 */
	public function getClass()
	{
		return ($ref = parent::getClass()) ? new ClassType($ref->getName()) : NULL;
	}



	/**
	 * @param  bool should be generic included to class name?
	 *
	 * @return string
	 */
	public function getClassName($withGeneric = FALSE)
	{
		$generic = NULL;
		if ($withGeneric === TRUE && $this->hasAnnotation() && $this->getAnnotation()->generic) {
			$generic = '<' . $this->getAnnotation()->generic . '>';
		}

		try {
			return ($ref = parent::getClass()) ? $ref->getName() . $generic : NULL;
		} catch (\ReflectionException $e) {
			if (preg_match('#Class (.+) does not exist#', $e->getMessage(), $m)) {
				return $m[1] . $generic;
			}
			throw $e;
		}
	}



	/**
	 * @return ClassType
	 */
	public function getDeclaringClass()
	{
		return ($ref = parent::getDeclaringClass()) ? new ClassType($ref->getName()) : NULL;
	}



	/**
	 * @return Method|GlobalFunction
	 */
	public function getDeclaringFunction()
	{
		return is_array($this->function)
			? new Method($this->function[0], $this->function[1])
			: new GlobalFunction($this->function);
	}



	/**
	 * @return bool
	 */
	public function isDefaultValueAvailable()
	{
		if (PHP_VERSION_ID === 50316) { // PHP bug #62988
			try {
				$this->getDefaultValue();
				return TRUE;
			} catch (\ReflectionException $e) {
				return FALSE;
			}
		}
		return parent::isDefaultValueAvailable();
	}



	public function __toString()
	{
		return '$' . parent::getName() . ' in ' . $this->getDeclaringFunction();
	}



	/********************* Nette\Annotations support ****************d*g**/



	/**
	 * Is method annotated?
	 *
	 * @return bool
	 */
	public function hasAnnotation()
	{
		return $this->getAnnotation() !== NULL;
	}



	/**
	 * Returns an annotation value.
	 *
	 * @return ParamAnnotation|NULL
	 */
	public function getAnnotation()
	{
		$res = AnnotationsParser::getAll($this->getDeclaringFunction());
		if (isset($res['param'][$this->getPosition()])) {
			$param = $res['param'][$this->getPosition()];
			return  $param->complete($this);
		}

		return NULL;
	}



	/********************* Nette\Object behaviour ****************d*g**/



	/**
	 * @return ClassType
	 */
	public /**/static/**/ function getReflection()
	{
		return new ClassType(/*5.2*$this*//**/get_called_class()/**/);
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
		ObjectMixin::remove($this, $name);
	}

}
