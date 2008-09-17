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
 * @package    Nette::Web
 * @version    $Id$
 */

/*namespace Nette::Web;*/

/*use Nette::Environment;*/



require_once dirname(__FILE__) . '/../Object.php';

require_once dirname(__FILE__) . '/../Web/IUser.php';



/**
 * Authentication and authorization.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Web
 */
class User extends /*Nette::*/Object implements IUser
{
	/** @var string  default role for unauthenticated user */
	public $guestRole = 'guest';

	/** @var string  default role for authenticated user without own identity */
	public $authenticatedRole = 'authenticated';

	/** @var string */
	public $cookieName = 'nette-authkey';

	/** @var string */
	public $cookieDomain;

	/** @var string */
	public $cookiePath;

	/** @var int */
	public $authExpiration = FALSE;

	/** @var int */
	public $identityExpiration = FALSE;

	/** @var Nette::Security::IAuthenticator */
	private $authenticationHandler;

	/** @var Nette::Security::IAuthorizator */
	private $authorizationHandler;

	/** @var SessionNamespace */
	private $session;



	/**
	 */
	public function __construct($name = NULL)
	{
		$this->cookiePath = Environment::getHttpRequest()->getUri()->basePath;
		$this->initSession();
	}



	/**
	 * @param  Nette::Security::IAuthenticator
	 * @return void
	 */
	public function setAuthenticationHandler(/*Nette::Security::*/IAuthenticator $handler)
	{
		$this->authenticationHandler = $handler;
	}



	/**
	 * @return Nette::Security::IAuthenticator
	 */
	final public function getAuthenticationHandler()
	{
		if ($this->authenticationHandler === NULL) {
			$this->authenticationHandler = Environment::getService('Nette::Security::IAuthenticator');
		}
		return $this->authenticationHandler;
	}



	/**
	 * @param  Nette::Security::IAuthorizator
	 * @return void
	 */
	public function setAuthorizationHandler(/*Nette::Security::*/IAuthorizator $handler)
	{
		$this->authorizationHandler = $handler;
	}



	/**
	 * @return Nette::Security::IAuthorizator
	 */
	final public function getAuthorizationHandler()
	{
		if ($this->authorizationHandler === NULL) {
			$this->authorizationHandler = Environment::getService('Nette::Security::IAuthorizator');
		}
		return $this->authorizationHandler;
	}



	/**
	 * Initializes $this->session.
	 * @return vois
	 */
	protected function initSession()
	{
		$this->session = $session = Environment::getSession('Nette.Web.User');

		if (!($session->identity instanceof /*Nette::Security::*/IIdentity)) {
			$session->identity = NULL;
		}

		if (!is_bool($session->authenticated)) {
			$session->authenticated = FALSE;
		}

		if ($session->authkey !== Environment::getHttpRequest()->getCookie($this->cookieName)) {
			$this->setAuthenticated(FALSE);
		}
	}



	/********************* Authentication ****************d*g**/



	/**
	 * Check the authenticated status.
	 * @param  string
	 * @param  string
	 * @param  mixed
	 * @return void
	 * @throws Nette::Security::AuthenticationException
	 */
	public function authenticate($username = NULL, $password = NULL, $extra = NULL)
	{
		$handler = $this->getAuthenticationHandler();
		if ($handler === NULL) {
			throw new /*::*/InvalidStateException('Authentization handler has not been set.');
		}

		$this->setAuthenticated(FALSE);

		$credentials = array(
			'username' => $username,
			'password' => $password,
			'extra' => $extra,
		);

		$this->setIdentity($handler->authenticate($credentials));
		$this->setAuthenticated(TRUE);
	}



	/**
	 * Removes the authentication flag from persistent storage.
	 * @param  bool  Clear the identity from persistent storage?
	 * @return void
	 */
	final public function signOut($clearIdentity = TRUE)
	{
		$this->setAuthenticated(FALSE);
		if ($clearIdentity) {
			$this->session->identity = NULL;
		}
	}



	/**
	 * Indicates whether this user is authenticated.
	 *
	 * @return bool true, if this user is authenticated, otherwise false.
	 */
	final public function isAuthenticated()
	{
		return $this->session->authenticated;
	}



	/**
	 * @return Nette::Security::IIdentity
	 */
	final public function getIdentity()
	{
		return $this->session->identity;
	}



	/**
	 * Set the authenticated status of this user.
	 *
	 * @param  bool A flag indicating the authenticated status of this user.
	 * @return void
	 */
	protected function setAuthenticated($value)
	{
		$value = ($value === TRUE);
		$session = $this->session;
		if ($session->authenticated === $value) return;

		$session->authenticated = $value;
		if ($value) {
			if (!$session->authkey) {
				$session->authkey = /*Nette::*/Tools::uniqueId();
			}
			if ($this->authExpiration) {
				$session->setExpiration($this->authExpiration, 'authkey');
			}
		} else {
			$session->authkey = NULL;
		}

		Environment::getHttpResponse()->setCookie(
			$this->cookieName,
			$session->authkey,
			HttpResponse::WINDOW,
			$this->cookiePath,
			$this->cookieDomain
		);
	}



	protected function setIdentity(/*Nette::Security::*/IIdentity $identity)
	{
		$this->session->identity = $identity;
		if ($this->identityExpiration) {
			$session->setExpiration($this->identityExpiration, 'identity');
		}
	}



	/********************* application support ****************d*g**/



	/**
	 * @deprecated
	 */
	public function storeRequest(/*Nette::Application::*/PresenterRequest $request)
	{
		trigger_error("Deprecated: use \$application->storeRequest() instead.", E_USER_WARNING);
		return Environment::getApplication()->storeRequest();
	}



	/**
	 * @deprecated
	 */
	public function restoreRequest($key)
	{
		trigger_error("Deprecated: use \$application->restoreRequest(\$key) instead.", E_USER_WARNING);
		Environment::getApplication()->restoreRequest($key);
	}



	/********************* Authorization ****************d*g**/



	/**
	 * Returns a role this user has been granted.
	 * @return array
	 */
	public function getRoles()
	{
		if (!$this->session->authenticated) {
			return array($this->guestRole);
		}

		if (!$this->session->identity) {
			return array($this->authenticatedRole);
		}

		return $this->session->identity->getRoles();
	}



	/**
	 * Returns a role this user has been granted.
	 * @param  string
	 * @return bool
	 */
	final public function isInRole($role)
	{
		return in_array($role, $this->getRoles(), TRUE);
	}



	/**
	 * Returns TRUE if and only if the user has access to the Resource.
	 *
	 * If either $resource is NULL, then the query applies to all Resources,
	 * respectively.
	 *
	 * @param  string  resource
	 * @param  string  privilege
	 * @return boolean
	 */
	public function isAllowed($resource = NULL, $privilege = NULL)
	{
		$handler = $this->getAuthorizationHandler();
		if (!$handler) {
			throw new /*::*/InvalidStateException("Authorization handler has not been set.");
		}

		foreach ($this->getRoles() as $role) {
			if ($handler->isAllowed($role, $resource, $privilege)) return TRUE;
		}

		return FALSE;
	}

}
