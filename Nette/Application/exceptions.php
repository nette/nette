<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application;

use Nette;


/**
 * The exception that is thrown when user attempts to terminate the current presenter or application.
 * This is special "silent exception" with no error message or code.
 */
class AbortException extends \Exception
{
}


/**
 * Application fatal error.
 */
class ApplicationException extends \Exception
{
}


/**
 * The exception that is thrown when a presenter cannot be loaded.
 */
class InvalidPresenterException extends \Exception
{
}


/**
 * Bad HTTP / presenter request exception.
 */
class BadRequestException extends \Exception
{
	/** @var int */
	protected $defaultCode = 404;


	public function __construct($message = '', $code = 0, \Exception $previous = NULL)
	{
		parent::__construct($message, $code < 200 || $code > 504 ? $this->defaultCode : $code, $previous);
	}

}


/**
 * Forbidden request exception - access denied.
 */
class ForbiddenRequestException extends BadRequestException
{
	/** @var int */
	protected $defaultCode = 403;

}
