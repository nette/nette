<?php

/**
 * This file is part of the Nette Framework (http://nette.texy.info/)
 *
 * Copyright (c) 2005-2007 David Grudl aka -dgx- <dave@dgx.cz>
 *
 * @version  $Revision: 51 $ $Date: 2007-04-11 20:34:53 +0200 (st, 11 IV 2007) $
 * @package  Nette
 */



class NUser implements IStatePersister
{
    /** @var string  storage namespace */
    protected $namespace = 'nette/user/';

    /** @var bool  is user authenticated? */
    protected $authenticated = FALSE;

    /** @var array */
    protected $attrs = array();

    protected $permissions = array();

    /** @var array  array of roles the user is assigned to */
    protected $roles = array();

    /** @var array  array of roles and permissions */
    protected $definitions = NULL;


    /**
     * loads from persistent storage
     */
    public function loadState()
    {
        /** @var NSessionStorageItem */
        $session = Nette::registry('session');
        $session->attach($this);

        /** @var NHttpRequest */
        $ns = $this->namespace;
        $this->roles = $session->read($ns . 'roles');
        $this->permissions = $session->read($ns . 'permissions');
        $this->attrs = $session->read($ns . 'attrs');
        $this->authenticated =
            $session->read($ns . 'authenticated')
            && ($session->read($ns . 'authkey') === NHttpRequest::getCookie('nette/user/authkey'));

        if (!$this->authenticated) {
            $this->roles = $this->permissions = array();
        }

        // Load RBAC role and permission definitions.
        $file = NETTE_CONFIG_DIR . '/rbac.config.php';
        if (is_file($file)) {
            $def = include $file;
            if ($def) $this->definitions = $def;
        }
    }


    /**
     * Saves into persistent storage
     */
    public function saveState($session)
    {
        /** @var NSessionStorageItem */
        $session = Nette::registry('session');
        $ns = $this->namespace;
        $session->write($ns . 'roles', $this->roles);
        $session->write($ns . 'permissions', $this->permissions);
        $session->write($ns . 'attrs', $this->attrs);
        $session->write($ns . 'authenticated', $this->authenticated);

        if ($this->authenticated) {
            $authkey = $session->read($ns . 'authkey');
            if (!$authkey) $authkey = NTools::uniqid();
        } else {
            $authkey = NULL;
        }
        $session->write($ns . 'authkey', $authkey);
        NHttpResponse::setCookie('nette/user/authkey', $authkey);
    }



    /**
     * Indicates whether or not this user is authenticated.
     *
     * @return bool true, if this user is authenticated, otherwise false.
     */
    public function isAuthenticated()
    {
        return $this->authenticated;
    }


    /**
     * Set the authenticated status of this user.
     *
     * @param bool A flag indicating the authenticated status of this user.
     * @return void
     */
    public function setAuthenticated($is)
    {
        $this->authenticated = $is === TRUE;
        if (!$this->authenticated) {
            $this->roles = $this->permissions = array();
        }
    }



    public function getAttr($key)
    {
        return isset($this->attrs[$key]) ? $this->attrs[$key] : NULL;
    }


    public function getAttrs()
    {
        return $this->attrs;
    }


    public function setAttr($key, $value)
    {
        if ($value === NULL)
            unset($this->attrs[$key]);
        else
            $this->attrs[$key] = $value;
    }


    /**
     * Add a permissions to this user.
     *
     * @param mixed Permission data.
     * @return void
     */
    public function grantPermissions($perms)
    {
    //    if (!is_array($perm))
        $perms = func_get_args();

        foreach ($perms as $perm) {
            if (is_string($perm)) $this->permissions[$perm] = TRUE;
        }
    }


    /**
     * Clear all permissions associated with this user.
     * @return void
     */
    public function revokeAllPermissions()
    {
        $this->permissions = array();
    }


    /**
     * Indicates whether or not this user has a permission.
     *
     * @param mixed Permission data.
     * @return bool true, if this user has the permission, otherwise false.
     */
    public function hasPermissions($perms)
    {
        $perms = func_get_args();
        foreach ($perms as $pAnd) {  // AND
            if (is_array($pAnd)) { // OR
                foreach($pAnd as $pOr) {
                    if (is_string($pOr) && isset($this->permissions[$pOr]))
                        continue 2;
                }
                return FALSE;
            }
            if (!is_string($pAnd) || !isset($this->permissions[$pAnd]))
                return FALSE;
        }
        return TRUE;
    }


    public function getPermissions()
    {
        return array_keys($this->permissions);
    }


    /**
     * Remove a permissions from this user.
     *
     * @param mixed Permission data.
     * @return void
     */
    public function revokePermissions($perms)
    {
        //if (!is_array($perms))
        $perms = func_get_args();

        foreach ($perms as $perm) {
            if (is_string($perm)) unset($this->permissions[$perm]);
        }
    }




    /**
     * Set a role membership for this user.
     * @param string  The role name to add to this user.
     */
    public function grantRole($role)
    {
        if (!is_string($role) || !isset($this->definitions[$role]) || isset($this->roles[$role]))
            return FALSE;

        $this->roles[$role] = TRUE;
        $def = $this->definitions[$role];
        do {
            foreach ($def['permissions'] as $perm)
                $this->permissions[$perm] = TRUE; // addPermission

            if (!isset($def['parent'])) break;

            $def = $this->definitions[ $def['parent'] ];
        } while (1);

        return TRUE;
    }


    /**
     * Revoke a role membership for this user.
     * @param string  The role name to remove from this user.
     */
    public function revokeRole($role)
    {
        if (!is_string($role) || !isset($this->definitions[$role]) || !isset($this->roles[$role]))
            return FALSE;

        unset($this->roles[$role]);

        // re-grant permissions
        $this->permissions = array();
        foreach ($this->roles as $role => $foo)
            $this->grantRole($role);
    }


    /**
     * Revoke all roles.
     */
    public function revokeAllRoles()
    {
        $this->permissions = array();
        $this->roles = array();
    }


    /**
     * Check whether or not a user is a member of a certain role.
     *
     * @param string  The role name to remove from this user.
     * @return bool  Whether or not the user is a member of the given role.
     */
    public function hasRole($role)
    {
        return is_string($role) && isset($this->roles[$role]);
    }


    /**
     * Return a list of roles this user has been granted.
     * @return array  An array of role names.
     */
    public function getRoles()
    {
        return array_keys($this->roles);
    }

}
