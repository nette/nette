<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Application
 */

/*namespace Nette\Application;*/



/**
 * Bad HTTP / presenter request exception.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Application
 */
class BadRequestException extends /*\*/Exception
{
	/** @var int */
	protected $defaultCode = 404;


	function __construct($message = '', $code = 0, /*\*/Exception $previous = NULL)
	{
		if ($code < 200 || $code > 504)	{
			$code = $this->defaultCode;
		}

		if (version_compare(PHP_VERSION , '5.3', '<')) {
			$this->previous = $previous;
			parent::__construct($message, $code);
		} else {
			parent::__construct($message, $code, $previous);
		}
	}

}
