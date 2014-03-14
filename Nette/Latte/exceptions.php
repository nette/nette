<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Latte;

use Nette;


/**
 * The exception occured during Latte compilation.
 *
 * @author     David Grudl
 */
class CompileException extends Nette\Templating\FilterException
{
}


class_alias('Nette\Latte\CompileException', 'Nette\Latte\ParseException');
