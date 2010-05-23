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

/*namespace Nette\Application;*/



/**
 * Redirects to new request.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Application
 */
class RedirectingResponse extends /*Nette\*/Object implements IPresenterResponse
{
	/** @var string */
	private $uri;

	/** @var int */
	private $code;



	/**
	 * @param  string  URI
	 * @param  int     HTTP code 3xx
	 */
	public function __construct($uri, $code = /*Nette\Web\*/IHttpResponse::S302_FOUND)
	{
		$this->uri = (string) $uri;
		$this->code = (int) $code;
	}



	/**
	 * @return string
	 */
	final public function getUri()
	{
		return $this->uri;
	}



	/**
	 * @return int
	 */
	final public function getCode()
	{
		return $this->code;
	}



	/**
	 * Sends response to output.
	 * @return void
	 */
	public function send()
	{
		/*Nette\*/Environment::getHttpResponse()->redirect($this->uri, $this->code);
	}

}
