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
 * Basic service builder.
 *
 * @author     David Grudl
 */
class ServiceBuilder extends Nette\Object implements IServiceBuilder
{
	/** @var string */
	private $class;



	public function __construct($class)
	{
		/*5.2* if ($a = strrpos($class, '\\')) $class = substr($class, $a + 1); // fix namespace*/
		$this->class = $class;
	}



	public function getClass()
	{
		return $this->class;
	}



	public function createService(Nette\DI\IContainer $container)
	{
		if (!class_exists($this->class)) {
			throw new Nette\InvalidStateException("Cannot instantiate service, class '$this->class' not found.");
		}
		return new $this->class;
	}

}
