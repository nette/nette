<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Application\UI;

use Nette,
	Nette\Application,
	Nette\Http,
	Nette\Security;



/**
 * Wrapper for services needed by Presenter.
 *
 * @author     David Grudl, Vašek Purchart
 *
 * @property-read Nette\Application\Application
 * @property-read Nette\Http\Context
 * @property-read Nette\Http\IRequest
 * @property-read Nette\Http\IResponse
 * @property-read Nette\Http\Session
 * @property-read Nette\Security\User
 */
class PresenterDependencies extends Nette\Object
{
	/** @var Nette\Application\Application */
	private $application;

	/** @var Nette\Http\Context */
	private $httpContext;

	/** @var Nette\Http\IRequest */
	private $httpRequest;

	/** @var Nette\Http\IResponse */
	private $httpResponse;

	/** @var Nette\Http\Session */
	private $session;

	/** @var Nette\Security\User */
	private $user;



	public function __construct(Application\Application $application, Http\Context $httpContext, Http\IRequest $httpRequest, Http\IResponse $httpResponse, Http\Session $session, Security\User $user)
	{
		$this->application = $application;
		$this->httpContext = $httpContext;
		$this->httpRequest = $httpRequest;
		$this->httpResponse = $httpResponse;
		$this->session = $session;
		$this->user = $user;
	}



	/**
	 * @return Nette\Application\Application
	 */
	public function getApplication()
	{
		return $this->application;
	}



	/**
	 * @return Nette\Http\Context
	 */
	public function getHttpContext()
	{
		return $this->httpContext;
	}



	/**
	 * @return Nette\Http\Request
	 */
	public function getHttpRequest()
	{
		return $this->httpRequest;
	}



	/**
	 * @return Nette\Http\IResponse
	 */
	public function getHttpResponse()
	{
		return $this->httpResponse;
	}



	/**
	 * @return Nette\Http\Session
	 */
	public function getSession()
	{
		return $this->session;
	}



	/**
	 * @return Nette\Security\User
	 */
	public function getUser()
	{
		return $this->user;
	}

}
