<?php

/**
 * This file is part of the Nette Framework (http://nette.texy.info/)
 *
 * Copyright (c) 2005-2007 David Grudl aka -dgx- <dave@dgx.cz>
 *
 * @version  $Revision: 51 $ $Date: 2007-04-11 20:34:53 +0200 (st, 11 IV 2007) $
 * @package  Nette
 */



class NFileStorage implements ILockingStorage
{
    /** @var string  directory path to storage */
    protected $path;

    /** @var int */
    protected $gcProbability = 1000;

    /** @var array  file resources */
    private $locks = array();


    /**
     * Inicializes new file-based storage engine
     * @param string  writable path
     */
    public function __construct($path)
    {
        if (!is_dir($path) || !is_writable($path))
            throw new NetteException("Path is not writable.");

        $this->path = $path . '/';

        if (lcg_value() * $this->gcProbability < 1) $this->gc();
    }


    public function read($id)
    {
        $s = NULL;
        if (isset($this->locks[$id])) {
            $s = stream_get_contents($this->locks[$id]);
        } else {
            // open and acquire shared lock
            $handle = @fopen($this->getFileName($id), 'rb', FALSE); // need @ due atomicity
            if ($handle) {
                if (flock($handle, LOCK_SH)) {
                    $s = stream_get_contents($handle);
                }
                fclose($handle);
            }
        }

        if (!is_string($s) || $s === '') return NULL;
        $value = unserialize($s);
        if ($value === FALSE && $s !== 'b:0;') return NULL;
        return $value;
    }


    public function write($id, $value, $meta=NULL)
    {
        $locked = isset($this->locks[$id]);
        if (!$locked) $this->lock($id, TRUE);

        $handle = $this->locks[$id];
        ftruncate($handle, 0);
        if ($value !== NULL) {
            $s = serialize($value);
            $ok = fwrite($handle, $s) === strlen($s);
            if (!$ok) ftruncate($handle, 0);
        } else $ok = TRUE;

        if (!$locked) $this->unlock($id);
        return $ok;
    }


    public function lock($id, $forWrite)
    {
        if (isset($this->locks[$id])) return TRUE;

        $handle = fopen($this->getFileName($id), 'a+b', FALSE);
        if ($handle) {
            if (flock($handle, $forWrite ? LOCK_EX : LOCK_SH)) {
                // stay locked
                $this->locks[$id] = $handle;
                return TRUE;
            }
            fclose($handle);
        }
        // throw exception
        return FALSE;
    }


    public function unlock($id)
    {
        if (isset($this->locks[$id])) {
            fclose($this->locks[$id]);
            unset($this->locks[$id]);
            return TRUE;
        }
        return FALSE;
    }


    /**
     * Generates full file path
     * @param string  ID
     * @return string  file path
     */
    protected function getFileName($id)
    {
        return $this->path . urlencode($id);
    }


    /**
     * Generic garbage collector
     */
    protected function gc()
    {
    }

}
