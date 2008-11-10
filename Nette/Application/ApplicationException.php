<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Application
 * @version    $Id$
 */

/*namespace Nette\Application;*/



/**
 * Application fatal error.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette\Application
 */
class ApplicationException extends /*\*/Exception
{

	function __construct($message = '', $code = 0, /*\*/Exception $previous = NULL)
	{
		if (version_compare(PHP_VERSION , '5.3', '<')) {
			$this->previous = $previous;
			parent::__construct($message, $code);

		} else {
			parent::__construct($message, $code, $previous);
		}
	}

}
