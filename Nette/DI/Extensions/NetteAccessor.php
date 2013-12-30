<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\DI\Extensions;

use Nette;


/**
 * @deprecated, for back compatibility
 */
class NetteAccessor extends Nette\Object
{
	private $container;


	public function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
	}


	public function __call($name, $args)
	{
		if (substr($name, 0, 6) === 'create') {
			$method = $this->container->getMethodName('nette.' . substr($name, 6));
			trigger_error("Factory accessing via nette->$name() is deprecated, use $method().", E_USER_DEPRECATED);
			return call_user_func_array(array($this->container, $method), $args);
		}
		throw new Nette\NotSupportedException;
	}


	public function &__get($name)
	{
		trigger_error("Service accessing via nette->$name is deprecated, use 'nette.$name'.", E_USER_DEPRECATED);
		$service = $this->container->getService("nette.$name");
		return $service;
	}

}
