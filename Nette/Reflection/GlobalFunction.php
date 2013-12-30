<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Reflection;

use Nette,
	Nette\ObjectMixin;


/**
 * Reports information about a function.
 *
 * @author     David Grudl
 * @property-read array $defaultParameters
 * @property-read bool $closure
 * @property-read Extension $extension
 * @property-read Parameter[] $parameters
 * @property-read bool $disabled
 * @property-read bool $deprecated
 * @property-read bool $internal
 * @property-read bool $userDefined
 * @property-read string $docComment
 * @property-read int $endLine
 * @property-read string $extensionName
 * @property-read string $fileName
 * @property-read string $name
 * @property-read string $namespaceName
 * @property-read int $numberOfParameters
 * @property-read int $numberOfRequiredParameters
 * @property-read string $shortName
 * @property-read int $startLine
 * @property-read array $staticVariables
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
	 * @deprecated
	 */
	public function toCallback()
	{
		return new Nette\Callback($this->value);
	}


	public function __toString()
	{
		return $this->getName() . '()';
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


	/**
	 * @return Parameter[]
	 */
	public function getParameters()
	{
		foreach ($res = parent::getParameters() as $key => $val) {
			$res[$key] = new Parameter($this->value, $val->getName());
		}
		return $res;
	}


	/********************* Nette\Object behaviour ****************d*g**/


	/**
	 * @deprecated
	 */
	public static function getReflection()
	{
		trigger_error(__METHOD__ . '() is deprecated.', E_USER_DEPRECATED);
		return new ClassType(get_called_class());
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
		ObjectMixin::set($this, $name, $value);
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
