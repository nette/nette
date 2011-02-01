<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Application;

use Nette;



/**
 * Redirects to new request.
 *
 * @author     David Grudl
 */
class RedirectingResponse extends Nette\Object implements IPresenterResponse
{
	/** @var string */
	private $uri;

	/** @var int */
	private $code;



	/**
	 * @param  string  URI
	 * @param  int     HTTP code 3xx
	 */
	public function __construct($uri, $code = Nette\Web\IHttpResponse::S302_FOUND)
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
		Nette\Environment::getHttpResponse()->redirect($this->uri, $this->code);
	}

}
