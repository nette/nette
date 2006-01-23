<?php

/**
 * This file is part of the Nette Framework (http://nette.texy.info/)
 *
 * Copyright (c) 2005-2007 David Grudl aka -dgx- <dave@dgx.cz>
 *
 * @version  $Revision: 51 $ $Date: 2007-04-11 20:34:53 +0200 (st, 11 IV 2007) $
 * @package  Nette
 */


interface IStorage
{
    /**
     * Reads entire file into a value. On failure returns NULL
     * @param string ID
     * @return mixed
     */
    public function read($id);

    /**
     * Write a value to a file
     * @param string ID
     * @param mixed  value
     * @param mixed  optional metainformations
     * @return bool  TRUE on success or FALSE on failure
     */
    public function write($id, $value, $meta=NULL);
}





interface ILockingStorage extends IStorage
{
    /**
     * Locks file
     * @param string ID
     * @param bool
     * @return bool  TRUE on success or FALSE on failure
     */
    public function lock($id, $forWrite);

    /**
     * Unlocks file
     * @param string ID
     * @return bool  TRUE on success or FALSE on failure
     */
    public function unlock($id);
}
