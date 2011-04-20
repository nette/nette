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
 * Reports information about a method's parameter.
 *
 * @author     David Grudl
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
	 * @return string
	 */
	public function getClassName()
	{
		return ($tmp = Nette\Utils\Strings::match($this, '#>\s+([a-z0-9_\\\\]+)#i')) ? $tmp[1] : NULL;
	}



	/**
	 * @return ClassType
	 */
	public function getDeclaringClass()
	{
		return ($ref = parent::getDeclaringClass()) ? new ClassType($ref->getName()) : NULL;
	}



	/**
	 * @return Method | FunctionReflection
	 */
	public function getDeclaringFunction()
	{
		return is_array($this->function)
			? new Method($this->function[0], $this->function[1])
			: new GlobalFunction($this->function);
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
