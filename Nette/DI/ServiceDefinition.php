<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\DI;

use Nette,
	Nette\Utils\Neon,
	Nette\Utils\Strings;



/**
 * Definition used by ContainerBuilder.
 *
 * @author     David Grudl
 */
class ServiceDefinition extends Nette\Object
{
	/** @var string  class or interface name */
	public $class;

	/** @var Statement */
	public $factory;

	/** @var Statement[] */
	public $setup = array();

	/** @var array */
	public $parameters = array();

	/** @var array */
	public $tags = array();

	/** @var mixed */
	public $autowired = TRUE;

	/** @var bool */
	public $shared = TRUE;

	/** @var bool */
	public $internal = FALSE;



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
		$this->factory = new Statement($factory, $args);
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



	public function addSetup($target, $args = NULL)
	{
		if (!is_array($args)) {
			$args = func_get_args();
			array_shift($args);
		}
		$this->setup[] = new Statement($target, $args);
		return $this;
	}



	public function setParameters(array $params)
	{
		$this->shared = $this->autowired = FALSE;
		$this->parameters = $params;
		return $this;
	}



	public function addTag($tag, $attrs = TRUE)
	{
		$this->tags[$tag] = $attrs;
		return $this;
	}



	public function setAutowired($on)
	{
		$this->autowired = $on;
		return $this;
	}



	public function setShared($on)
	{
		$this->shared = (bool) $on;
		$this->autowired = $this->shared ? $this->autowired : FALSE;
		return $this;
	}



	public function setInternal($on)
	{
		$this->internal = (bool) $on;
		return $this;
	}



	/**
	 * @internal
	 * @return string
	 */
	public function serialize($name)
	{
		$lines = array($name . ':');
		if ($this->factory) {
			if ($this->factory->entity !== $this->class) {
				$lines[] = "\tclass: " . $this->class;
				$lines[] = "\tfactory: " . $this->factory;

			} else {
				$lines[] = "\tclass: " . $this->factory;
			}

		} elseif ($this->class) {
			$lines[] = "\tclass: " . $this->class;
		}
		if ($this->parameters) {
			$lines[] = "\tparameters:";
			$lines[] = Strings::indent(Neon::encode($this->parameters, Neon::BLOCK), 2);
		}
		if ($this->setup) {
			$lines[] = "\tsetup:";
			foreach ($this->setup as $call) {
				$lines[] = "\t\t - " . $call;
			}
		}
		if ($this->tags) {
			$lines[] = "\ttags:";
			$lines[] = Strings::indent(Neon::encode($this->tags, Neon::BLOCK), 2);
		}
		if ($this->autowired === FALSE) {
			$lines[] = "\tautowired: false";
		}
		if ($this->shared === FALSE) {
			$lines[] = "\tshared: false";
		}
		if ($this->internal === TRUE) {
			$lines[] = "\tinternal: true";
		}

		return implode("\n", $lines);
	}



	/**
	 * @internal
	 * @return string
	 */
	public function __toString()
	{
		return $this->serialize('service');
	}

}
