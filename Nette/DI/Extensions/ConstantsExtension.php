<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\DI\Extensions;

use Nette;


/**
 * Constant definitions.
 *
 * @author     David Grudl
 */
class ConstantsExtension extends Nette\DI\CompilerExtension
{

	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		foreach ($this->getConfig() as $name => $value) {
			$class->methods['initialize']->addBody('define(?, ?);', array($name, $value));
		}
	}

}
