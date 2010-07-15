<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Reflection
 */

namespace Nette\Reflection;

use Nette,
	Nette\ObjectMixin,
	Nette\Annotations;



/**
 * Reports information about a method's parameter.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Reflection
 */
class ParameterReflection extends \ReflectionParameter
{
	/** @var mixed */
	private $function;


	public function __construct($function, $parameter)
	{
		parent::__construct($this->function = $function, $parameter);
	}



	/**
	 * @return Nette\Reflection\ClassReflection
	 */
	public function getClass()
	{
		return ($ref = parent::getClass()) ? ClassReflection::import($ref) : NULL;
	}



	/**
	 * @return Nette\Reflection\ClassReflection
	 */
	public function getDeclaringClass()
	{
		return ($ref = parent::getDeclaringClass()) ? ClassReflection::import($ref) : NULL;
	}



	/**
	 * @return Nette\Reflection\MethodReflection | Nette\Reflection\FunctionReflection
	 */
	public function getDeclaringFunction()
	{
		return is_array($this->function) ? new MethodReflection($this->function[0], $this->function[1]) : new FunctionReflection($this->function);
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
