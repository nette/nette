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
 * @package    Nette::Loaders
 */

/*namespace Nette::Loaders;*/


require_once dirname(__FILE__) . '/../Loaders/AutoLoader.php';



/**
 * Nette auto loader is responsible for loading classes and interfaces.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Loaders
 * @version    $Revision$ $Date$
 */
class RobotLoader extends AutoLoader
{
    /** @var array  */
    public $scanDirs = array();

    /** @var string  comma separated wildcards */
    public $ignoreDirs = '.svn, .cvs, *.old, *.bak, *.tmp';

    /** @var string  comma separated wildcards */
    public $acceptFiles = '*.php, *.php5';

    /** @var string  */
    public $cacheFile = '%tempDir%/nette_autoload.bin';  // or 'safe://%tempDir%/nette_autoload.bin' ???

    /** @var array */
    private $list = NULL;

    /** @var string */
    private $acceptMask;

    /** @var string */
    private $ignoreMask;



    /**
     * Handles autoloading of classes or interfaces.
     * @param  string
     * @return void
     */
    public function tryLoad($type)
    {
        // is initialized?
        if ($this->list === NULL) {
            $this->loadCache();
        }

        /**/// fix for namespaced classes/interfaces in PHP < 5.3
        if ($a = strrpos($type, ':')) $type = substr($type, $a + 1);/**/

        $type = strtolower($type);
        if (isset($this->list[$type])) {
            self::includeOnce($this->list[$type]);
        }
    }



    /**
     * Rebuilds class list cache.
     * @return void
     */
    public function rebuild()
    {
        $this->acceptMask = self::wildcards2re($this->acceptFiles);
        $this->ignoreMask = self::wildcards2re($this->ignoreDirs);
        $this->list = array();

        foreach ($this->scanDirs as $dir) {
            $dir = realpath($dir);
            if ($dir) $this->scanDir($dir);
        }
        $this->saveCache();
    }



    /**
     * Add class and file name to the list.
     * @param  string
     * @param  string
     * @return void
     */
    public function add($class, $file)
    {
        $class = strtolower($class);
        if (isset($this->list[$class]) && $this->list[$class] !== $file) {
            // throwing exception is not possible
            trigger_error("Ambiguous class '$class' resolution; defined in $file and in " . $this->list[$class] . ".", E_USER_ERROR);
        }
        $this->list[$class] = $file;
    }



    /**
     * Loads cache.
     * @return void
     */
    protected function loadCache()
    {
        // require_once dirname(__FILE__) . '/SafeStream.php';
        //$file = Environment::expand($this->cacheFile);
        $file = $this->cacheFile;
        $data = @unserialize(file_get_contents($file));

        if (is_array($data) &&
            $data['scanDirs'] === $this->scanDirs &&
            $data['ignoreDirs'] === $this->ignoreDirs &&
            $data['acceptFiles'] === $this->acceptFiles) {
            $this->list = $data['list'];
        } else {
            $this->rebuild();
        }
    }



    /**
     * Saves cache.
     * @return void
     */
    protected function saveCache()
    {
        //file_put_contents('temp', var_export($this->list, true));;

        $data = serialize(array(
            'list' => $this->list,
            'scanDirs' => $this->scanDirs,
            'acceptFiles' => $this->acceptFiles,
            'ignoreDirs' => $this->ignoreDirs,
        ));

        //$file = Environment::expand($this->cacheFile);
        $file = $this->cacheFile;
        $len = file_put_contents($file, $data);
        if ($len !== strlen($data)) {
            // throwing exception is not possible
            trigger_error("Unable to save autoload list to file '$file'.", E_USER_ERROR);
        }
    }



    /**
     * Scan one directory for PHP files and subdirectories.
     * @param  string
     * @return void
     */
    private function scanDir($dir)
    {
        // scan directory
        $d = dir($dir);
        if (!$d) return;

        if (!defined('T_NAMESPACE')) {
            define('T_NAMESPACE', -1);
        }

        while (FALSE !== ($entry = $d->read())) {
            if ($entry == '.' || $entry == '..') continue;

            $path = $dir . DIRECTORY_SEPARATOR . $entry;
            if (!is_readable($path)) continue;

            // process subdirectories
            if (is_dir($path)) {
                // check ignore mask
                if (!preg_match($this->ignoreMask, $entry)) {
                    $this->scanDir($path);
                }
                continue;
            }

            if (is_file($path)) {
                // check include mask
                if (!preg_match($this->acceptMask, $entry)) continue;

                $expected = FALSE;
                $namespace = '';

                foreach (token_get_all(file_get_contents($path)) as $token)
                {
                    if (is_array($token)) {
                        switch ($token[0]) {
                        case T_NAMESPACE:
                        case T_CLASS:
                        case T_INTERFACE:
                            $expected = $token[0];
                            $name = '';
                            continue 2;

                        case T_COMMENT:
                        case T_DOC_COMMENT:
                        case T_WHITESPACE:
                            continue 2;

                        case T_DOUBLE_COLON:
                        case T_STRING:
                            if ($expected) {
                                $name .= $token[1];
                            }
                            continue 2;
                        }
                    }

                    if ($expected === T_NAMESPACE) {
                        $namespace = $name . '::';
                        $expected = FALSE;

                    } elseif ($expected) {
                        $this->add($namespace . $name, $path);
                        $expected = FALSE;
                    }
                }
            }

        } // while

        $d->close();
    }



    /**
     * Converts comma separated wildcards to regular expression.
     * @param  string
     * @return string
     */
    private static function wildcards2re($wildcards)
    {
        $mask = array();
        foreach (explode(',', $wildcards) as $wildcard) {
            $wildcard = trim($wildcard);
            $wildcard = addcslashes($wildcard, '.\\+[^]$(){}=!><|:#');
            $wildcard = strtr($wildcard, array('*' => '.*', '?' => '.?'));
            $mask[] = $wildcard;
        }
        return '#^(' . implode('|', $mask) . ')$#i';
    }



    /**/
    public static function factory($config = NULL, $class = NULL)
    {
        parent::factory($config, __CLASS__);
    }
    /**/

}
