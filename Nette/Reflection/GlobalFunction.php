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
 * Reports information about a function.
 *
 * @author     David Grudl
 */
class GlobalFunction extends \ReflectionFunction
{
	/** @var string|Closure */
	private $value;


	public function __construct($name)
	{
		parent::__construct($this->value = $name);
	}



	/**
	 * @return array
	 */
	public function getDefaultParameters()
	{
		return Method::buildDefaultParameters(parent::getParameters());
	}



	/**
	 * Invokes function using named parameters.
	 * @param  array
	 * @return mixed
	 */
	public function invokeNamedArgs($args)
	{
		return $this->invokeArgs(Method::combineArgs($this->getDefaultParameters(), $args));
	}



	/**
	 * @return Nette\Callback
	 */
	public function toCallback()
	{
		return new Nette\Callback($this->value);
	}



	public function __toString()
	{
		return 'Function ' . $this->getName() . '()';
	}



	public function getClosure()
	{
		return $this->isClosure() ? $this->value : NULL;
	}



	/********************* Reflection layer ****************d*g**/



	/**
	 * @return Extension
	 */
	public function getExtension()
	{
		return ($name = $this->getExtensionName()) ? new Extension($name) : NULL;
	}



	public function getParameters()
	{
		foreach ($res = parent::getParameters() as $key => $val) {
			$res[$key] = new Parameter($this->value, $val->getName());
		}
		return $res;
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
