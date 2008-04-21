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

require_once dirname(__FILE__) . '/../Caching/ICacheStorage.php';



/**
 * Cache file storage
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette
 * @version    $Revision$ $Date$
 */
class FileCache extends /*Nette::*/Object implements ICacheStorage
{
    /** @var string */
    private $dir;

    /** @var string  last query cache */
    private $key;

    /** @var mixed  last query cache */
    private $data;



    public function __construct($dir = NULL)
    {
        if ($dir === NULL) {
            $dir = /*Nette::*/Environment::getVariable('cacheDir');
            require_once dirname(__FILE__) . '/../IO/SafeStream.php';
        }

        if (!is_dir($dir) || !is_writable($dir)) {
            throw new /*::*/InvalidStateException("Temporary directory '$dir' is not writable.");
        }

        $this->dir = $dir . '/';
    }



    /**
     * Read from cache.
     * @param  string key
     * @return void
     */
    public function read($key)
    {
        if ($this->key === $key) {
            return $this->data;
        }

        $this->key = $key;
        $cache = @unserialize(file_get_contents($this->getFile($key))); // intentionally @
        if (is_array($cache)) {
            return $this->data = $cache['data'];
        } else {
            return $this->data = NULL;
        }
    }



    /**
     * Writes item into the cache.
     * @param  string key
     * @param  mixed
     * @param  array
     * @param  int
     * @param  int
     * @return void
     */
    public function write($key, $data, $tags, $lifeTime, $priority)
    {
        if ($data === NULL) {
            $this->remove($key);
            return;
        }

        $this->key = $key;
        $this->data = $data;

        $s = serialize(array(
            'data' => $data
        ));
        $file = $this->getFile($key);
        $len = file_put_contents($file, $s); // intentionally @
        if ($len !== strlen($s)) {
            unlink($file);
            $this->data = NULL;
        }
    }



    /**
     * Removes item from the cache.
     * @param  string key
     * @return void
     */
    public function remove($key)
    {
        $this->key = $key;
        $this->data = NULL;
        @unlink($this->getFile($key)); // intentionally @
    }



    /**
     * Removes items from the cache by dependencies.
     * @param  array tags
     * @return void
     */
    public function clean($tags)
    {
        $this->key = $this->data = NULL;
        throw new /*::*/NotImplementedException();
    }


    private function getFile($key)
    {
        return $this->dir . urlencode($key) . '.cache';
    }

}
