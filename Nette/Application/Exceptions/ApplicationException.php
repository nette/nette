<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Application
 */

/*namespace Nette\Application;*/



/**
 * Application fatal error.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Application
 */
class ApplicationException extends /*\*/Exception
{
	/**/
	public function __construct($message = '', $code = 0, /*\*/Exception $previous = NULL)
	{
		if (version_compare(PHP_VERSION , '5.3', '<')) {
			$this->previous = $previous;
			parent::__construct($message, $code);
		} else {
			parent::__construct($message, $code, $previous);
		}
	}
	/**/
}
