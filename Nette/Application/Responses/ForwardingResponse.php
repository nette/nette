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
 * Forwards to new request.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Application
 */
class ForwardingResponse extends Nette\Object implements IPresenterResponse
{
	/** @var PresenterRequest */
	private $request;



	/**
	 * @param  PresenterRequest  new request
	 */
	public function __construct(PresenterRequest $request)
	{
		$this->request = $request;
	}



	/**
	 * @return PresenterRequest
	 */
	final public function getRequest()
	{
		return $this->request;
	}



	/**
	 * Sends response to output.
	 * @return void
	 */
	public function send()
	{
	}

}
