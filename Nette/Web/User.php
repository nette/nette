<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com/
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com/
 * @category   Nette
 * @package    Nette::Web
 */

/*namespace Nette::Web;*/
/*use Nette::Environment;*/


require_once dirname(__FILE__) . '/../Object.php';

require_once dirname(__FILE__) . '/../Security/Identity.php';

require_once dirname(__FILE__) . '/../Security/AuthenticationException.php';



/**
 * Authentication and authorization.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Web
 * @version    $Revision$ $Date$
 */
class User extends /*Nette::*/Object
{
    /** @var array  default role for unauthenticated user */
    public static $guestRole = 'guest';

    /** @var array  default role for authenticated user without own identity */
    public static $authenticatedRole = 'authenticated';

    /** @var string  storage namespace */
    private $namespace;

    /** @var Nette::Security::IAuthenticator */
    private $authenticationHandler;

    /** @var Nette::Security::IAuthorizator */
    private $authorizationHandler;

    /** @var SessionNamespace */
    private $session;

    /** @var string */
    public $cookieDomain;

    /** @var string */
    public $cookiePath;



    /**
     */
    public function __construct($name = NULL)
    {
        $this->namespace = $name === NULL ? $this->getClass() : $name;
        $this->cookiePath = Environment::getHttpRequest()->getBasePath();
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
        $this->session = $session = Session::getNamespace($this->namespace);

        if (!($session->identity instanceof /*Nette::Security::*/IIdentity)) {
            $session->identity = NULL;
        }

        if (!is_bool($session->authenticated)) {
            $session->authenticated = FALSE;
        }

        if ($session->authkey !== Environment::getHttpRequest()->getCookie($this->namespace . 'authkey')) {
            $this->setAuthenticated(FALSE);
        }
    }



    /********************* Authentication ****************d*g**/



    /**
     * Check the authenticated status.
     * @param  string
     * @param  string
     * @return void
     * @throws Nette::Security::AuthenticationException
     */
    public function authenticate($username = NULL, $password = NULL)
    {
        $handler = $this->getAuthenticationHandler();
        if ($handler === NULL) {
            throw new /*::*/InvalidStateException('Missing authentization handler.');
        }

        $this->setAuthenticated(FALSE);

        $credentials = array(
            'username' => $username,
            'password' => $password,
        );

        $this->setIdentity($handler->authenticate($credentials));
        $this->setAuthenticated(TRUE);
    }



    /**
     * Removes the authentication flag from persistent storage.
     * *
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
     * @return IIdentity
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
                require_once dirname(__FILE__) . '/../Tools.php';
                $session->authkey = /*Nette::*/Tools::uniqueId();
            }
        } else {
            $session->authkey = NULL;
        }

        Environment::getHttpResponse()->setCookie(
            $this->namespace . 'authkey',
            $session->authkey,
            HttpResponse::WINDOW,
            $this->cookiePath,
            $this->cookieDomain
        );
    }



    protected function setIdentity(IIdentity $identity)
    {
        $this->session->identity = $identity;
    }



    /********************* Authorization ****************d*g**/



    /**
     * Returns a role this user has been granted.
     * @return array
     */
    public public function getRoles()
    {
        if (!$this->session->authenticated) {
            return array(self::$guestRole);
        }

        if (!$this->session->identity) {
            return array(self::$authenticatedRole);
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
        return array_search($role, $this->getRoles(), TRUE);
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
            throw new /*::*/InvalidStateException('Missing authorization handler.');
        }

        foreach ($this->getRoles() as $role) {
            if ($handler->isAllowed($role, $resource, $privilege)) return TRUE;
        }

        return FALSE;
    }

}
