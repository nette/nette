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

	/** @var Statement */
	public $factory;

	/** @var array of Statement */
	public $setup = array();

	/** @var array */
	public $tags = array();

	/** @var mixed */
	public $autowired = TRUE;



	public function setClass($class, array $args = NULL)
	{
		if (!$this->factory || $this->factory->entity === $this->class) {
			$this->setFactory($class, $args);
		}
		$this->class = $class;
		return $this;
	}



	public function setFactory($factory, array $args = NULL)
	{
		$this->factory = new Statement($factory, $args);
		return $this;
	}



	public function addSetup($target, $args = NULL)
	{
		$this->setup[] = new Statement($target, $args);
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

}
