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
    /** @var array */
    public $scanDirs;

    /** @var string  comma separated wildcards */
    public $ignoreDirs = '.svn, .cvs, *.old, *.bak, *.tmp';

    /** @var string  comma separated wildcards */
    public $acceptFiles = '*.php, *.php5';

    /** @var bool  experimental */
    public $displaceNetteLoader = TRUE;

    /** @var Nette::Caching::Cache  */
    private $cache;

    /** @var array */
    private $list = NULL;

    /** @var string */
    private $acceptMask;

    /** @var string */
    private $ignoreMask;



    public function __construct(/*Nette::Caching::*/Cache $cache = NULL)
    {
        $this->cache = $cache;
        $this->addDirectory(dirname(__FILE__) . '/..');
    }



    /**
     * Handles autoloading of classes or interfaces.
     * @param  string
     * @return void
     */
    public function tryLoad($type)
    {
        if ($this->list === NULL) {
            $this->list = array(); // prevents cycling

            $cache = $this->cache ? $this->cache : /*Nette::*/Environment::getCache('Nette:RobotLoader');
            $data = $cache['data'];
            $opt = array($this->scanDirs, $this->ignoreDirs, $this->acceptFiles);

            if ($data['opt'] === $opt) {
                $this->list = $data['list'];
            } else {
                $this->rebuild();
                $cache['data'] = array(
                    'list' => $this->list,
                    'opt' => $opt,
                );
            }

            if ($this->displaceNetteLoader) {
                foreach (self::getLoaders() as $loader) {
                    if ($loader instanceof NetteLoader) {
                        $loader->unregister();
                    }
                }
            }
        }

        /**/// fix for namespaced classes/interfaces in PHP < 5.3
        if ($a = strrpos($type, ':')) $type = substr($type, $a + 1);/**/

        $type = strtolower($type);
        if (isset($this->list[$type])) {
            self::includeOnce($this->list[$type]);
            self::$count++;
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

        foreach (array_unique($this->scanDirs) as $dir) {
            $this->scanDirectory($dir);
        }

        //$this->saveCache();
    }



    /**
     * Add directory (or directories) to list.
     * @param  string|array
     * @return void
     */
    public function addDirectory($path)
    {
        foreach ((array) $path as $val) {
            $this->scanDirs[] = realpath($val);
        }
    }



    /**
     * Add class and file name to the list.
     * @param  string
     * @param  string
     * @return void
     */
    public function addClass($class, $file)
    {
        $class = strtolower($class);
        if (isset($this->list[$class]) && $this->list[$class] !== $file) {
            // throwing exception is not possible
            trigger_error("Ambiguous class '$class' resolution; defined in $file and in " . $this->list[$class] . ".", E_USER_ERROR);
        }
        $this->list[$class] = $file;
    }



    /**
     * Scan a directory for PHP files, subdirectories and 'netterobots.txt' file
     * @param  string
     * @return void
     */
    private function scanDirectory($dir)
    {
        $dir = realpath($dir);
        if (!$dir) return;
        $iterator = dir($dir);
        if (!$iterator) return;

        if (!defined('T_NAMESPACE')) {
            define('T_NAMESPACE', -1);
        }

        $disallow = array();
        if (is_file($dir . '/netterobots.txt')) {
            foreach (file($dir . '/netterobots.txt') as $s) {
                if (preg_match('#^disallow\\s*:\\s*(\\S+)#i', $s, $m)) {
                    $disallow[trim($m[1], '/')] = TRUE;
                }
            }
            if (isset($disallow[''])) return;
        }

        while (FALSE !== ($entry = $iterator->read())) {
            if ($entry == '.' || $entry == '..' || isset($disallow[$entry])) continue;

            $path = $dir . '/' . $entry;
            if (!is_readable($path)) continue;

            // process subdirectories
            if (is_dir($path)) {
                // check ignore mask
                if (!preg_match($this->ignoreMask, $entry)) {
                    $this->scanDirectory($path);
                }
                continue;
            }

            if (is_file($path) && preg_match($this->acceptMask, $entry)) {
                $this->scanScript($path);
            }
        }

        $iterator->close();
    }



    /**
     * Analyse PHP file.
     * @param  string
     * @return void
     */
    private function scanScript($file)
    {
        if (!defined('T_NAMESPACE')) {
            define('T_NAMESPACE', -1);
        }

        $expected = FALSE;
        $namespace = '';
        $level = 0;

        foreach (token_get_all(file_get_contents($file)) as $token)
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

                    } elseif ($token[1] === 'namespace') { // PHP < 5.3
                        $expected = T_NAMESPACE;
                        $name = '';
                    }
                    continue 2;
                }
            }

            if ($expected) {
                if ($expected === T_NAMESPACE) {
                    $namespace = $name . '::';
                } elseif ($level === 0) {
                    $this->addClass($namespace . $name, $file);
                }
                $expected = FALSE;
            }

            if (is_array($token)) {
                if ($token[0] === T_CURLY_OPEN || $token[0] === T_DOLLAR_OPEN_CURLY_BRACES) {
                    $level++;
                }
            } elseif ($token === '{') {
                $level++;
            } elseif ($token === '}') {
                $level--;
            }
        }
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

}
