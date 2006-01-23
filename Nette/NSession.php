<?php

/**
 * This file is part of the Nette Framework (http://nette.texy.info/)
 *
 * Copyright (c) 2005-2007 David Grudl aka -dgx- <dave@dgx.cz>
 *
 * @version  $Revision: 51 $ $Date: 2007-04-11 20:34:53 +0200 (st, 11 IV 2007) $
 * @package  Nette
 */



class NSessionStorage extends NFileStorage
{
    protected function gc()
    {
        /*
        $dir = dir($this->path);
        while (FALSE !== ($entry = $dir->read())) {
            if (strncmp($entry, $this->namespace) && is_file($entry)) {
                fileatime();
                ...
            }
        }
        $dir->close();
        */
    }
}




class NSession extends NArrayStorage /*implements IStatePersister*/
{
    /** @var SplObjectStorage */
    protected $observers;

    /** @var string  storage namespace  */
    protected $namespace = 'sess_';

    /** @var string */
    protected $storageId;

    /** @var ILockingStorage */
    protected $storage;

    /** @var bool */
    protected $opened = FALSE;



    public function __construct()
    {
        $this->storage = Nette::registry('sessionStorage');
        $this->observers = new SplObjectStorage();
        Nette::attach($this);
    }


    public function loadState($cookieName='session')
    {
        // additional protection against Session Hijacking & Fixation
        $verify = @md5(  // sorry @
            $_SERVER['HTTP_ACCEPT_CHARSET'] .
            $_SERVER['HTTP_ACCEPT_ENCODING'] .
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] .
            $_SERVER['HTTP_USER_AGENT']
        );

        $cookie = NHttpRequest::getCookie($cookieName);
        if ($cookie && preg_match('#^[0-9a-z]{20,}$#', $cookie)) {
            $this->open($cookie);
            need($this->arr['nette/session/verify']);
            if ($this->arr['nette/session/verify'] !== $verify) {
                $this->storage->unLock($this->storageId);
                $this->opened = FALSE;
            }
        }

        if (!$this->opened) {
            $id = NTools::uniqid();
            // TODO: httponly
            // TODO: session_set_cookie_params
            // TODO: session_cache_expire - Return current cache expire
            // TODO: session_cache_limiter - Get and/or set the current cache limiter
            NHttpResponse::setCookie($cookieName, $id, time() + 500);
            $this->open($id);
            $this->arr['nette/session/verify'] = $verify;
        }

        need($this->arr['nette/session/counter']);
        $this->arr['nette/session/counter']++;
    }

    public function saveState()
    {
        $this->storage->write($this->storageId, $this->arr);
    }


    public function open($id)
    {
        if ($this->opened) return FALSE;
        $this->storageId = $this->namespace . $id;
        $this->storage->lock($this->storageId, TRUE);
        $this->arr = $this->storage->read($this->storageId);
        $this->opened = TRUE;
        return TRUE;
    }


    public function close()
    {
        if (!$this->opened) return FALSE;
        foreach ($this->observers as $obj)
            $obj->saveState($this);

        $this->storage->write($this->storageId, $this->arr);
        $this->storage->unLock($this->storageId);
        $this->opened = FALSE;
        return TRUE;
    }


    public function isOpened($id)
    {
        return $this->opened;
    }


    public function destroy()
    {
        $this->arr = NULL;
        $this->close();
    }


    public function attach(IStatePersister $observer)
    {
        $this->observers->attach($observer);
    }


    public function detach(IStatePersister $observer)
    {
        $this->observers->detach($observer);
    }


    public function update($subject, $event=NULL)
    {
        if ($event === 'end') $this->close();
    }

}





interface IStatePersister
{
    /**
     * Loads state from a persistent storage.
     */
    public function loadState();

    /**
     * Saves state into a persistent storage.
     * @param object
     */
    public function saveState($subject);
}
