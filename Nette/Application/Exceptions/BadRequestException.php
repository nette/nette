<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Application
 */

namespace Nette\Application;

use Nette;



/**
 * Bad HTTP / presenter request exception.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Application
 */
class BadRequestException extends \Exception
{
	/** @var int */
	protected $defaultCode = 404;


	public function __construct($message = '', $code = 0, \Exception $previous = NULL)
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
