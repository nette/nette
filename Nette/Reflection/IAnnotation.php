<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Reflection;

use Nette;


/**
 * Code annotation.
 *
 * @author     David Grudl
 */
interface IAnnotation
{

	function __construct(array $values);

}
