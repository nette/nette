<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Reflection;

use Nette;



/**
 * Basic annotation implementation.
 *
 * @author     David Grudl
 */
class Annotation extends Nette\Object implements IAnnotation
{

	public function __construct(array $values)
	{
		foreach ($values as $k => $v) {
			$this->$k = $v;
		}
	}



	/**
	 * Returns default annotation.
	 * @return string
	 */
	public function __toString()
	{
		return $this->value;
	}

}
