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
 * Reports information about a function.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette
 */
class FunctionReflection extends /*\*/ReflectionFunction
{

	public function __toString()
	{
		return 'Function ' . $this->getName() . '()';
	}



	/********************* Reflection layer ****************d*g**/



	/**
	 * @return Nette\Reflection\FunctionReflection
	 */
	public static function import(/*\*/ReflectionFunction $ref)
	{
		return new self($ref->getName());
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
