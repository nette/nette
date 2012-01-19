<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Application;

use Nette;



/**
 * The exception that is thrown when user attempts to terminate the current presenter or application.
 * This is special "silent exception" with no error message or code.
 *
 * @author     David Grudl
 */
class AbortException extends \Exception
{
}
