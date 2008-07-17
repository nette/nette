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
 * @package    Nette::Application
 * @version    $Id$
 */

/*namespace Nette::Application;*/



/**
 * Application fatal error.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Application
 */
class ApplicationException extends /*::*/Exception implements /*Nette::*/ICausedException
{
	/** @var ::Exception */
	private $cause;



	/**
	 * @param string  text describing the exception
	 * @param int     code describing the exception
	 * @param ::Exception  instance that caused the current exception
	 */
	public function __construct($message = NULL, $code = 0, /*::*/Exception $cause = NULL)
	{
		$this->cause = $cause;
		parent::__construct($message, $code);
	}



	/**
	 * Gets the Exception instance that caused the current exception.
	 * @return ::Exception
	 */
	public function getCause()
	{
		return $this->cause;
	}



	/**
	 * Returns string represenation of exception.
	 * @return string
	 */
	public function __toString()
	{
		$s = parent::__toString();

		if ($this->cause !== NULL) {
			$s .= "\nCaused by " . $this->cause->__toString();
		}

		return $s;
	}

}
