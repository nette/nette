<?php

/**
 * This file is part of the Nette Framework (http://nette.texy.info/)
 *
 * Copyright (c) 2005-2007 David Grudl aka -dgx- <dave@dgx.cz>
 *
 * @version  $Revision: 54 $ $Date: 2007-06-12 13:19:17 +0200 (út, 12 VI 2007) $
 * @package  Nette
 */




class NAutoload extends NObject
{
    /** @var array */
    private $list = NULL;

    /** @var array  */
    public $scanDirs = array();



    /**
     * Handles __autoload
     * @param string class name to load
     * @return void
     */
    public function load($name)
    {
        // is initialized?
        if ($this->list === NULL) {
            /** @var NStorage */
            $storage = Nette::registry('storage');
            $val = $storage->read('nette_autoload');
            if (is_array($val) && $val[1] === $this->scanDirs) {
                $this->list = $val[0];
            } else {
                $this->rebuild();
                if (!$storage->write('nette_autoload', array($this->list, $this->scanDirs)))
                    throw new NetteException("Can't store autoload list."); // fatal error!
            }
        }

        $name = strtolower($name);
        if (isset($this->list[$name])) {
            NTools::loadScript($this->list[$name], TRUE);
        }
    }



    /**
     * Rebuilds class list
     * @return void
     */
    private function rebuild()
    {
        $this->list = array();
        foreach ($this->scanDirs as $dir) {
            $dir = realpath($dir);
            if ($dir) $this->scanDir($dir);
        }
    }



    /**
     * Scan one directory for PHP files and subdirectories
     * @param string
     * @return void
     */
    private function scanDir($dir)
    {
        // scan directory
        $d = dir($dir);
        if (!$d) return;

        while (FALSE !== ($entry = $d->read())) {
            if ($entry == '.' || $entry == '..') continue;

            $entry = $dir . '/' . $entry;
            if (!is_readable($entry)) continue;

            // process subdirectories
            if (is_dir($entry)) {
                $this->scanDir($entry);
                continue;
            }

            // analyse script
            if (is_file($entry) && substr($entry, -4) == '.php') {
                $expected = FALSE;
                foreach (token_get_all(file_get_contents($entry)) as $token)
                {
                    if (is_array($token)) {
                        switch ($token[0]) {
                        case T_CLASS:
                        case T_INTERFACE:
                            $expected = TRUE;
                            continue 2;

                        case T_COMMENT:
                        case T_DOC_COMMENT:
                        case T_WHITESPACE:
                            continue 2;

                        case T_STRING:
                            if ($expected) {
                                $class = strtolower($token[1]);
                                // if (isset($this->list[$class])) duplicity detected
                                $this->list[$class] = $entry;
                            }
                        }
                    }
                    $expected = FALSE;
                }
            }

        } // while
        $d->close();
    }



}
