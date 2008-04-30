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
 * @package    Nette
 */

/*namespace Nette;*/


/**/define('__DIR__', dirname(__FILE__));/**/


/**
 * Nette environment and configuration.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette
 * @version    $Revision$ $Date$
 */
final class Environment
{
    /** environments */
    const DEVELOPMENT = 'development';
    const PRODUCTION = 'production';
    const CONSOLE = 'console';
    const LAB = 'lab';

    /** modes: */
    const NORMAL_MODE = 0;
    const OFF_MODE = 1;
    const DEBUG_MODE = 2;
    const PERFORMANCE_MODE = 4;

    /** variables */
    const LANG = 'lang';

    /** @var string */
    private static $name;

    /** @var string  the mode of current application */
    private $mode = self::NORMAL_MODE;

    /** @var Config */
    private static $config;

    /** @var IServiceLocator */
    private static $locator;

    /** @var array */
    private static $vars = array(
        'encoding' => array('UTF-8', FALSE),
        'lang' => array('en', FALSE),
        'netteDir' => array(__DIR__, FALSE),
        'cacheBase' => array('%tempDir%/cache-', TRUE),
        'tempDir' => array('%appDir%/temp', TRUE),
        'logDir' => array('%appDir%/log', TRUE),
        'libsDir' => array('%appDir%/libs', TRUE),
        'templatesDir' => array('%appDir%/templates', TRUE),
        'presentersDir' => array('%appDir%/presenters', TRUE),
        'componentsDir' => array('%appDir%/components', TRUE),
        'modelsDir' => array('%appDir%/models', TRUE),
    );

    public static $defaultServices = array(
        'Nette::IServiceLocator' => /*Nette::*/'ServiceLocator',
        'Nette::Web::IHttpRequest' => 'Nette::Web::HttpRequest',
        'Nette::Web::IHttpResponse' => 'Nette::Web::HttpResponse',
        'Nette::Application::IRouter' => 'Nette::Application::MultiRouter',
        'Nette::Caching::ICacheStorage' => array(__CLASS__, 'factoryCacheStorage'),
    );



    /**
     * Static class - cannot be instantiated.
     */
    final public function __construct()
    {
        throw new /*::*/LogicException("Cannot instantiate static class " . get_class($this));
    }



    /**
     * Sets the current environment name.
     * @param  string
     * @return void
     * @throws ::InvalidStateException
     */
    public static function setName($name)
    {
        if (self::$name === NULL) {
            self::$name = (string) $name;
            self::setVariable('envName', self::$name, FALSE);

        } else {
            throw new /*::*/InvalidStateException('Environment name has been already set.');
        }
    }



    /**
     * Returns the current environment name.
     * @return string
     */
    public static function getName()
    {
        if (self::$name === NULL) {
            if (defined('ENVIRONMENT')) {
                self::setName(ENVIRONMENT);

            } elseif (self::isConsole()) {
                self::setName(self::CONSOLE);

            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                // detect by IP address
                $oct = explode('.', $_SERVER['REMOTE_ADDR']);
                self::setName((count($oct) === 4) && ($oct[0] === '10' || $oct[0] === '127' || ($oct[0] === '171' && $oct[1] > 15 && $oct[1] < 32)
                    || ($oct[0] === '169' && $oct[1] === '254') || ($oct[0] === '192' && $oct[1] === '168'))
                    ? self::DEVELOPMENT
                    : self::PRODUCTION);

            } else {
                self::setName(self::PRODUCTION);
            }
        }

        return self::$name;
    }



    /**
     * Sets the mode.
     *
     * @param  int mode
     * @return void
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }



    /**
     * Returns the mode.
     *
     * @param  int mode mask
     * @return int|bool
     */
    final public function getMode($mask = NULL)
    {
        return $mask === NULL ? $this->mode : (bool) $this->mode & $mask;
    }



    /**
     * Detects console (non-HTTP) mode.
     * @return bool
     */
    public static function isConsole()
    {
        return PHP_SAPI === 'cli';
    }



    /********************* environment variables ****************d*g**/



    /**
     * Sets the environment variable.
     * @param  string
     * @param  mixed
     * @param  bool
     * @return void
     */
    public static function setVariable($name, $value, $expand = TRUE)
    {
        self::$vars[$name] = array($value, (bool) $expand);
    }



    /**
     * Returns the value of an environment variable or $default if there is no element set.
     * @param  string
     * @param  mixed  default value to use if key not found
     * @return mixed
     */
    public static function getVariable($name, $default = NULL)
    {
        if (isset(self::$vars[$name])) {
            list($var, $exp) = self::$vars[$name];
            if ($exp) {
                $var = self::expand($var);
                self::$vars[$name] = array($var, FALSE);
            }
            return $var;

        } else {
            // convert from camelCaps (or PascalCaps) to ALL_CAPS
            $const = strtoupper(preg_replace('#(.)([A-Z]+)#', '$1_$2', $name));
            $list = get_defined_constants(TRUE);
            if (isset($list['user'][$const])) {
                self::$vars[$name] = array($list['user'][$const], FALSE);
                return $list['user'][$const];
            } else {
                return $default;
            }
        }
    }



    /**
     * Returns expanded variable.
     * @param  string
     * @return string
     */
    public static function expand($var)
    {
        if (is_string($var) && strpos($var, '%') !== FALSE) {
            return preg_replace_callback('#%([a-z0-9_-]*)%#i', array(__CLASS__, 'expandCb'), $var);
        }
        return $var;
    }



    /**
     * @see self::expand()
     * @param  array
     * @return string
     */
    private static function expandCb($m)
    {
        list(, $var) = $m;
        if ($var === '') return '%';

        static $livelock;
        if (isset($livelock[$var])) {
            throw new /*::*/InvalidStateException("Circular reference detected for variables: "
                . implode(', ', array_keys($livelock)) . ".");
        }

        try {
            $livelock[$var] = TRUE;
            $val = self::getVariable($var);
            unset($livelock[$var]);
        } catch (Exception $e) {
            $livelock = array();
            throw $e;
        }

        if ($val === NULL) {
            throw new /*::*/InvalidStateException("Unknown environment variable '$var'.");
        }
        return $val;
    }



    /********************* service locator ****************d*g**/



    /**
     * Get initial instance of service locator (experimental).
     * @return IServiceLocator
     */
    public static function getServiceLocator()
    {
        if (self::$locator === NULL) {
            self::$locator = new self::$defaultServices['Nette::IServiceLocator'];
            foreach (self::$defaultServices as $name => $service) {
                self::$locator->addService($service, $name);
            }
        }
        return self::$locator;
    }



    /**
     * Gets the service.
     * @param  string
     * @return object
     */
    static public function getService($name)
    {
        return self::getServiceLocator()->getService($name);
    }



    /**
     * @return Nette::Web::IHttpRequest
     */
    public static function getHttpRequest()
    {
        return self::getServiceLocator()->getService('Nette::Web::IHttpRequest');
    }



    /**
     * @return Nette::Web::IHttpResponse
     */
    public static function getHttpResponse()
    {
        return self::getServiceLocator()->getService('Nette::Web::IHttpResponse');
    }



    /**
     * @return Nette::Application::Application
     */
    public static function getApplication()
    {
        return self::getServiceLocator()->getService('Nette::Application::Application');
    }



    /**
     * @return Nette::Security::IUser
     */
    public static function getUser()
    {
        return self::getServiceLocator()->getService('Nette::Security::User');
    }



    /********************* service factories ****************d*g**/



    /**
     * @param  string
     * @return Nette::Caching::Cache
     */
    public static function getCache($namespace = '')
    {
        return new /*Nette::Caching::*/Cache(
            self::getService('Nette::Caching::ICacheStorage'),
            $namespace
        );
    }



    /**
     * @param  string
     * @return Nette::Caching::ICacheStorage
     */
    public static function factoryCacheStorage()
    {
        return new /*Nette::Caching::*/FileStorage(self::getVariable('cacheBase'));
    }



    /********************* global configuration ****************d*g**/



    /**
     * Loads global configuration from file and process it.
     * @param  string|Config  file name or Config object
     * @param  bool
     * @return Config
     */
    public static function loadConfig($file = NULL, $useCache = NULL)
    {
        if ($file === NULL) {
            $file = '%appDir%/config.ini';
        }

        $name = self::getName();

        if ($useCache === NULL) {
            $useCache = $name === self::PRODUCTION;
        }

        $cache = $useCache ? self::getCache('Nette:Environment') : NULL;

        if (isset($cache[$name])) {
            list(self::$vars, self::$config, self::$locator) = $cache[$name];
            $cfg = self::$config;

        } else {
            if ($file instanceof Config) {
                self::$config = $file;
                $file = NULL;
            } else {
                $file = self::expand($file);
                self::$config = Config::fromFile($file, $name, 0);
            }

            $cfg = self::$config;

            // process environment variables
            if ($cfg->variable instanceof Config) {
                foreach ($cfg->variable as $key => $value) {
                    self::setVariable($key, $value);
                }
            }

            if (isset($cfg->set->include_path)) {
                $cfg->set->include_path = strtr($cfg->set->include_path, ';', PATH_SEPARATOR);
            }

            $cfg->expand();
            $cfg->setReadOnly();

            // process services
            $locator = self::getServiceLocator();
            if ($cfg->service instanceof Config) {
                foreach ($cfg->service as $key => $value) {
                    $locator->addService($value, $key);
                }
            }

            // save cache
            if ($cache) {
                $cache->save($name, array(self::$vars, self::$config, self::$locator), array('files' => $file));
            }
        }


        // check temporary directory - TODO: discuss
        /*
        $dir = self::getVariable('tempDir');
        if ($dir && !(is_dir($dir) && is_writable($dir))) {
            trigger_error("Temporary directory '$dir' is not writable", E_USER_NOTICE);
        }
        */

        // process ini settings
        if ($cfg->set instanceof Config) {
            if (!function_exists('ini_set')) {
                throw new /*::*/NotSupportedException('Function ini_set() is not enabled.');
                /* or try to use workaround?
                "date.timezone" => "date_default_timezone_set($value);",
                "iconv.internal_encoding" => "iconv_set_encoding('internal_encoding', $value);",
                "mbstring.internal_encoding" => "mb_internal_encoding($value);",
                "include_path" => "set_include_path(strtr($value, ';', PATH_SEPARATOR));",
                */
            }

            foreach ($cfg->set as $key => $value) {
                ini_set($key, $value);
            }
        }

        // execute services - TODO: discuss
        /*
        if ($cfg->run) {
            $run = $cfg->run->toArray();
            ksort($run);
            foreach ($run as $value) {
                $a = strrpos($value, ':');
                $service = substr($value, 0, $a - 1);
                $service = $locator->getService($service);
                $method = substr($value, $a + 1);
                $service->$method();
            }
        }
        */

        return $cfg;
    }



    /**
     * Returns the global configuration.
     * @return Config
     */
    public static function getConfig($key = NULL, $default = NULL)
    {
        if (func_num_args()) {
            return self::$config->get($key, $default);
        } else {
            return self::$config;
        }
    }

}
