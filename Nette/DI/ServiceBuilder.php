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



	function __construct($class)
	{
		$this->class = $class;
	}



	function createService(Nette\DI\IContainer $container)
	{
		$class = $this->class;
		/*5.2* if ($a = strrpos($class, '\\')) $class = substr($class, $a + 1); // fix namespace*/
		if (!class_exists($class)) {
			throw new AmbiguousServiceException("Cannot instantiate service, class '$class' not found.");
		}
		return new $class;
	}

}
