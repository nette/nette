<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\DI;

use Nette;


/**
 * Definition used by ContainerBuilder.
 *
 * @author     David Grudl
 *
 * @method string getClass()
 * @method Statement getFactory()
 * @method ServiceDefinition setSetup(Statement[])
 * @method Statement[] getSetup()
 * @method ServiceDefinition setParameters(array)
 * @method array getParameters()
 * @method ServiceDefinition setTags(array)
 * @method array getTags()
 * @method ServiceDefinition setAutowired(bool)
 * @method bool isAutowired()
 * @method ServiceDefinition setInject(bool)
 * @method bool getInject()
 * @method ServiceDefinition setImplement(string)
 * @method string getImplement()
 * @method ServiceDefinition setImplementType(string)
 * @method string getImplementType()
 */
class ServiceDefinition extends Nette\Object
{
	/** @var string  class or interface name */
	private $class;

	/** @var Statement */
	private $factory;

	/** @var Statement[] */
	private $setup = array();

	/** @var array */
	public $parameters = array();

	/** @var array */
	private $tags = array();

	/** @var bool */
	private $autowired = TRUE;

	/** @var bool */
	private $inject = TRUE;

	/** @var string  interface name */
	private $implement;

	/** @internal @var string  create | get */
	private $implementType;


	public function setClass($class, array $args = array())
	{
		$this->class = $class;
		if ($args) {
			$this->setFactory($class, $args);
		}
		return $this;
	}


	public function setFactory($factory, array $args = array())
	{
		$this->factory = $factory instanceof Statement ? $factory : new Statement($factory, $args);
		return $this;
	}


	public function setArguments(array $args = array())
	{
		if ($this->factory) {
			$this->factory->arguments = $args;
		} else {
			$this->setClass($this->class, $args);
		}
		return $this;
	}


	public function addSetup($target, array $args = array())
	{
		$this->setup[] = new Statement($target, $args);
		return $this;
	}


	public function addTag($tag, $attrs = TRUE)
	{
		$this->tags[$tag] = $attrs;
		return $this;
	}


	/** @deprecated */
	public function setShared($on)
	{
		trigger_error(__METHOD__ . '() is deprecated.', E_USER_DEPRECATED);
		$this->autowired = $on ? $this->autowired : FALSE;
		return $this;
	}


	/** @deprecated */
	public function isShared()
	{
		trigger_error(__METHOD__ . '() is deprecated.', E_USER_DEPRECATED);
	}

}
