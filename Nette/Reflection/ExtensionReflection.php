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
