<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\DI;

use Nette;



/**
 * Definition used by ContainerBuilder.
 *
 * @author     David Grudl
 */
class ServiceDefinition extends Nette\Object
{
	/** @var string  class or interface name */
	public $class;

	/** @var string|array  Factory::create */
	public $factory;

	/** @var array */
	public $arguments;

	/** @var array of array(callback|methodName, arguments) */
	public $calls = array();

	/** @var array */
	public $tags = array();

	/** @var mixed */
	public $autowired = TRUE;



	public function setClass($class)
	{
		$this->class = $class;
		return $this;
	}



	public function setArguments(array $args)
	{
		$this->arguments = $args;
		return $this;
	}



	public function setFactory($factory)
	{
		$this->factory = $factory;
		return $this;
	}



	public function addCall($method, array $args = NULL)
	{
		$this->calls[] = array($method, $args);
		return $this;
	}



	public function addTag($tag, $attrs = NULL)
	{
		$this->tags[$tag] = (array) $attrs;
		return $this;
	}



	public function setAutowired($on)
	{
		$this->autowired = $on;
		return $this;
	}

}
