<?php

/**
 * This file is part of the Nette Framework (http://nette.texy.info/)
 *
 * Copyright (c) 2005-2007 David Grudl aka -dgx- <dave@dgx.cz>
 *
 * @version  $Revision: 51 $ $Date: 2007-04-11 20:34:53 +0200 (st, 11 IV 2007) $
 * @package  Nette
 */



class NArrayStorage implements IStorage
{
    protected $arr = array();


    public function read($key)
    {
        if (isset($this->arr[$key]))
            return $this->arr[$key];
        else
            return NULL;
    }


    public function write($key, $value, $meta=NULL)
    {
        if ($value === NULL)
            unset($this->arr[$key]);
        else
            $this->arr[$key] = $value;
        return TRUE;
    }

}
