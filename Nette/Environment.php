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

    /** variables */
    const LANG = 'lang';

    /** @var string */
    private static $name;

    /** @var Config */
    private static $config;

    /** @var IServiceLocator */
    private static $locator;

    /** @var array */
    private static $vars = array(
        'encoding' => array('UTF-8', 0),
        'lang' => array('en', 0),
        'tempDir' => array('%appDir%/temp', 1),
        'logDir' => array('%appDir%/log', 1),
        'libsDir' => array('%appDir%/libs', 1),
        'templatesDir' => array('%appDir%/templates', 1),
        'presentersDir' => array('%appDir%/presenters', 1),
        'componentsDir' => array('%appDir%/components', 1),
        'modelsDir' => array('%appDir%/models', 1),
    );

    public static $defaultServices = array(
        'Nette::IServiceLocator' => 'Nette::ServiceLocator',
        'Nette::Web::IHttpRequest' => 'Nette::Web::HttpRequest',
        'Nette::Web::IHttpResponse' => 'Nette::Web::HttpResponse',
        'Nette::Application::IRouter' => 'Nette::Application::MultiRouter',
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
                self::$name = ENVIRONMENT;

            } elseif (self::isConsole()) {
                self::$name = self::CONSOLE;

            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                // detect by IP address
                $oct = explode('.', $_SERVER['REMOTE_ADDR']);
                self::$name = (count($oct) === 4) && ($oct[0] === '10' || $oct[0] === '127' || ($oct[0] === '171' && $oct[1] > 15 && $oct[1] < 32)
                    || ($oct[0] === '169' && $oct[1] === '254') || ($oct[0] === '192' && $oct[1] === '168'))
                    ? self::DEVELOPMENT
                    : self::PRODUCTION;

            } else {
                self::$name = self::PRODUCTION;
            }
        }

        return self::$name;
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
        self::$vars[$name] = array($value, $expand ? 1 : 0);
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
                self::$vars[$name] = array($var, 0);
            }
            return $var;

        } else {
            // convert from camelCaps (PascalCaps) to ALL_CAPS
            $const = strtoupper(preg_replace('#(.)([A-Z]+)#', '$1_$2', $name));
            $list = get_defined_constants(TRUE);
            if (isset($list['user'][$const])) {
                self::$vars[$name] = array($list['user'][$const], 0);
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
        if (!is_string($var) || strpos($var, '%') === FALSE) return $var;

        static $infLoop;
        if (isset($infLoop[$var])) {
            throw new Exception("Infinite loop detected for variables "
                . implode(', ', array_keys($infLoop)) . ".");
        }

        $infLoop[$var] = TRUE;
        $res = preg_replace_callback('#%([a-zA-Z0-9_-]+)%#', array(__CLASS__, 'expandCb'), $var);
        unset($infLoop[$var]);

        return $res;
    }



    /**
     * @see self::expand()
     * @param  array
     * @return string
     */
    private static function expandCb($m)
    {
        $val = self::getVariable($m[1]);
        if ($val === NULL) {
            throw new Exception("Unknown environment variable $m[0].");
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
            $type = self::$defaultServices['Nette::IServiceLocator'];
            if ($type === 'Nette::ServiceLocator') { // default one
                $type = 'ServiceLocator'; // PHP < 5.3
            }
            self::$locator = new $type;

            foreach (self::$defaultServices as $type => $service) {
                self::$locator->addService($service/*, $type*/);
            }
        }

        return self::$locator;
    }



    /**
     * Gets the service.
     * @param  string
     * @return object
     */
    static public function getService($type)
    {
        return self::getServiceLocator()->getService($type);
    }



    /**
     * @return Nette::Web::IHttpRequest
     */
    public static function getHttpRequest()
    {
        //return self::getServiceLocator()->getService('Nette::Web::IHttpRequest');
        return self::getServiceLocator()->getService('Nette::Web::HttpRequest');
    }



    /**
     * @return Nette::Web::IHttpResponse
     */
    public static function getHttpResponse()
    {
        //return self::getServiceLocator()->getService('Nette::Web::IHttpResponse');
        return self::getServiceLocator()->getService('Nette::Web::HttpResponse');
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



    /********************* global configuration ****************d*g**/



    /**
     * Loads global configuration from file and process it.
     * @param  string|Config  file name or Config object
     * @return void
     */
    public static function loadConfig($fileName = '%appDir%/config.ini')
    {
        if ($fileName instanceof Config) {
            self::$config = $fileName;

        } else {
            require_once dirname(__FILE__) . '/Config.php';
            self::$config = Config::fromFile(self::expand($fileName), self::getName(), TRUE);
        }

        $cfg = self::$config;

        // process environment variables
        if ($cfg->var instanceof Config) {
            foreach ($cfg->var as $key => $value) {
                self::setVariable($key, $value);
            }
        }

        $dir = self::getVariable('tempDir');
        if ($dir && !(is_dir($dir) && is_writable($dir))) {
            trigger_error("Temporary directory '$dir' is not writable", E_USER_NOTICE);
        }

        // process ini settings
        if ($cfg->set instanceof Config) {
            if (!function_exists('ini_set')) {
                throw Exception('Function ini_set() is not enabled.');

                /* or try to use workaround?
                "date.timezone" => "date_default_timezone_set($value);",
                "iconv.internal_encoding" => "iconv_set_encoding('internal_encoding', $value);",
                "mbstring.internal_encoding" => "mb_internal_encoding($value);",
                "include_path" => "set_include_path(strtr($value, ';', PATH_SEPARATOR));",

                if (isset($wa[$key])) {
                    eval($wa[$key]);
                } else {
                    throw Exception('');
                }
                */
            }

            foreach ($cfg->set as $key => $value) {
                if ($key === 'include_path') {
                    ini_set($key, self::expand(strtr($value, ';', PATH_SEPARATOR)));
                } else {
                    ini_set($key, self::expand($value));
                }
            }
        }

        // process services
        $locator = self::getServiceLocator();
        if ($cfg->service instanceof Config) {
            foreach ($cfg->service as $key => $value) {
                $locator->addService($value, $key);
            }
        }

        // execute services
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
