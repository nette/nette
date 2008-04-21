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
 * @package    Nette::Caching
 */

/*namespace Nette::Caching;*/



require_once dirname(__FILE__) . '/../Object.php';



/**
 * Implements the cache for a application.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette
 * @version    $Revision$ $Date$
 */
class Cache extends /*Nette::*/Object implements ArrayAccess
{
    /** @var ICacheStorage */
    private $storage;



    public function __construct(ICacheStorage $storage = NULL)
    {
        if ($storage === NULL) {
            require_once dirname(__FILE__) . '/../Caching/FileCache.php';
            $this->storage = new FileCache;
        } else {
            $this->storage = $storage;
        }
    }



    /**
     * Inserts (replaces) item into the cache.
     * @param  string key
     * @param  mixed
     * @param  array
     * @param  int
     * @return void
     * @throws ::InvalidArgumentException
     */
    public function add($key, $data, $tags = NULL, $lifeTime = 0)
    {
        if (!is_string($key)) { // prevents NULL
            throw new /*::*/InvalidArgumentException('Key must be a string.');
        }

        $cache = $this->offsetGet($key);
        if ($cache === NULL) {
            $this->storage->write($key, $data, $tags, $lifeTime, NULL);
            return NULL;

        } else {
            return $cache;
        }
    }



    /**
     * Inserts (replaces) item into the cache.
     * @param  string key
     * @param  mixed
     * @param  array
     * @param  int
     * @return void
     * @throws ::InvalidArgumentException
     */
    public function insert($key, $data, $tags = NULL, $lifeTime = 0)
    {
        if (!is_string($key)) {
            throw new /*::*/InvalidArgumentException('Key must be a string.');
        }

        $this->storage->write($key, $data, $tags, $lifeTime, NULL);
    }


    /********************* interface ::ArrayAccess ****************d*g**/



    /**
     * Inserts (replaces) item into the cache (::ArrayAccess implementation).
     * @param  string key
     * @param  mixed
     * @return void
     * @throws ::InvalidArgumentException
     */
    public function offsetSet($key, $data)
    {
        if (!is_string($key)) { // prevents NULL
            throw new /*::*/InvalidArgumentException('Key must be a string.');
        }

        $this->storage->write($key, $data, NULL, 0, NULL);
    }



    /**
     * Retrieves the specified item from the cache or NULL if the key is not found (::ArrayAccess implementation).
     * @param  string key
     * @return mixed|NULL
     * @throws ::InvalidArgumentException
     */
    public function offsetGet($key)
    {
        if (!is_string($key)) {
            throw new /*::*/InvalidArgumentException('Key must be a string.');
        }

        return $this->storage->read($key);
    }



    /**
     * Exists item in cache? (::ArrayAccess implementation).
     * @param  string key
     * @return bool
     * @throws ::InvalidArgumentException
     */
    public function offsetExists($key)
    {
        if (!is_string($key)) {
            throw new /*::*/InvalidArgumentException('Key must be a string.');
        }

        return $this->storage->read($key) !== NULL;
    }



    /**
     * Removes the specified item from the cache.
     * @param  string key
     * @return void
     * @throws ::InvalidArgumentException
     */
    public function offsetUnset($key)
    {
        if (!is_string($key)) {
            throw new /*::*/InvalidArgumentException('Key must be a string.');
        }

        $this->storage->remove($key);
    }

}
