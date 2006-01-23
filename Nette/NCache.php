<?php

/**
 * This file is part of the Nette Framework (http://nette.texy.info/)
 *
 * Copyright (c) 2005-2007 David Grudl aka -dgx- <dave@dgx.cz>
 *
 * @version  $Revision: 51 $ $Date: 2007-04-11 20:34:53 +0200 (st, 11 IV 2007) $
 * @package  Nette
 */



interface ICacheStorage extends IStorage
{
    /**
     * Invalidates part of cache
     * @param array tags
     */
    public function clean($tags);
}



class NCacheStorage extends NFileStorage implements ICacheStorage
{

    public function read($id)
    {
        $data = parent::read($id);
        if (isset($data['value'])) return $data['value'];
        return NULL;
    }


    public function write($id, $value, $tags=NULL, $lifeTime=0)
    {
        $data = array(
            'value' => $value,
            'lifeTime'  => $lifeTime,
            'tags'  => (array) $tags,
        );
        return parent::write($id, $data);
    }

    protected function gc()
    {
        // todo
    }

    public function clean($tags)
    {
        // not implemented yet
    }

}




class NCache extends NObject
{
    /** @var string  storage namespace  */
    protected $namespace = 'cache_';

    /** @var string */
    protected $storageId;

    /** @var ICacheStorage */
    protected $storage;

    /** @var mixed */
    protected $content;


    public function __construct($keys)
    {
        if (!is_array($keys)) $keys = func_get_args();
        $this->storageId = $this->namespace . implode('_', $keys);
        $this->storage = Nette::registry('cacheStorage');
        $this->content = $this->storage->read($this->storageId);

        if (NETTE_MODE === 'DEBUG') $this->content = NULL;
    }


    public function exists()
    {
        return $this->content !== NULL;
    }


    public function read()
    {
        return $this->content;
    }


    public function write($value, $tags=NULL, $lifeTime=0)
    {
        $this->content = $value;
        return $this->storage->write($this->storageId, $value, $tags, $lifeTime);
    }


    public function remove()
    {
        $this->content = NULL;
        return $this->storage->write($this->storageId, NULL);
    }

}
