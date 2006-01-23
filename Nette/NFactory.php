<?php

/**
 * This file is part of the Nette Framework (http://nette.texy.info/)
 *
 * Copyright (c) 2005-2007 David Grudl aka -dgx- <dave@dgx.cz>
 *
 * @version  $Revision: 51 $ $Date: 2007-04-11 20:34:53 +0200 (st, 11 IV 2007) $
 * @package  Nette
 */


class NFactory extends NObject
{
    /**
     * Returns the router object.
     * @return NRouter
     */
    public function router()
    {
        return new NRouter;
    }


    /**
     * Returns the user object.
     * @return NUser
     */
    public function user()
    {
        $obj = new NUser;
        $obj->loadState();
        return $obj;
    }


    /**
     * Returns the generic storage object.
     * @return ILockingStorage
     */
    public function storage()
    {
        return new NFileStorage(NETTE_TEMP_DIR);
    }


    /**
     * Returns the session object.
     * @return NSession
     */
    public function session()
    {
        $session = new NSession();
        $session->loadState();
        return $session;
    }

    /**
     * Returns the session storage object.
     * @return ILockingStorage
     */
    public function sessionStorage()
    {
        return new NSessionStorage(NETTE_TEMP_DIR);
    }


    /**
     * Returns the cache object.
     * @return NCache
     */
    public function cache($keys)
    {
        return new NCache($keys);
    }


    /**
     * Returns the cache storage object.
     * @return ICacheStorage
     */
    public function cacheStorage()
    {
        return new NCacheStorage(NETTE_TEMP_DIR);
    }


    /**
     * Returns the autoload manager
     * @return NAutoload
     */
    public function autoload()
    {
        $obj = new NAutoload;
        $obj->scanDirs[] = NETTE_APP_DIR;
        $obj->scanDirs[] = NETTE_LIB_DIR;
        return $obj;
    }


}
