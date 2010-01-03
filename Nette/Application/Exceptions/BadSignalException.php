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



require_once dirname(__FILE__) . '/../../Application/Exceptions/BadRequestException.php';



/**
 * Signal exception.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Application
 */
class BadSignalException extends BadRequestException
{
	/** @var int */
	protected $defaultCode = 403;

}
