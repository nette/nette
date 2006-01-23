<?php

/**
 * This file is part of the Nette Framework (http://nette.texy.info/)
 *
 * Copyright (c) 2005-2007 David Grudl aka -dgx- <dave@dgx.cz>
 *
 * @version  $Revision: 54 $ $Date: 2007-06-12 13:19:17 +0200 (út, 12 VI 2007) $
 * @package  Nette
 */

/* Check for required version of PHP
 * PHP 5.0.3 - getNumberOfRequiredParameters, get_class_methods, is_subclass_of accepts string
 * PHP 5.0.4 - getDefaultProperties segfaults
 * PHP 5.0.5 - bug #30702 - cannot initialize class variable from class constant
 * PHP 5.1.0 - property_exists() security
 * PHP 5.1.0 - ReflectionProperty::getDocComment(), bug #36308
 * PHP 5.1.2 - header() security
 * PHP 5.1.3 .. 5.1.6 - getDeclaringClass() bug #36434, #39001
 * PHP 5.1.0 - ReflectionClass::getStaticPropertyValue, date_default_timezone_set
 */


if (version_compare(PHP_VERSION , '5.1.2', '<'))
    die('Nette needs PHP 5.1.2 or newer.');


/**
 * Check constants
 */
if (!defined('NETTE_WWW_DIR')) die("Define NETTE_WWW_DIR constant.");
if (!defined('NETTE_APP_DIR')) die("Define NETTE_APP_DIR constant.");
if (!defined('NETTE_TEMPLATE_DIR')) die("Define NETTE_TEMPLATE_DIR constant.");
if (!defined('NETTE_TEMP_DIR')) die("Define NETTE_TEMP_DIR constant.");
if (!is_writable(NETTE_TEMP_DIR)) die('Directory NETTE_TEMP_DIR must be writable.');
if (!defined('NETTE_LOG_DIR')) die("Define NETTE_LOG_DIR constant.");
if (!defined('NETTE_CONFIG_DIR')) die("Define NETTE_CONFIG_DIR constant.");
if (!defined('NETTE_WWW_URI')) die("Define NETTE_WWW_URI constant.");
if (!defined('NETTE_MODE')) define('NETTE_MODE', 'NORMAL');


/**
 * PHP configuration
 */
if (ini_get('arg_separator.output') !== '&') ini_set('arg_separator.output', '&');
iconv_set_encoding('internal_encoding', 'UTF-8');
date_default_timezone_set('Europe/Prague');


/**
 * Include base part of framework
 */
define('NETTE_DIR', dirname(__FILE__));
require_once NETTE_DIR . '/NObject.php';
require_once NETTE_DIR . '/NException.php';
require_once NETTE_DIR . '/NTools.php';
require_once NETTE_DIR . '/NAutoload.php';
require_once NETTE_DIR . '/NStorage.php';
require_once NETTE_DIR . '/storages/NFileStorage.php';
require_once NETTE_DIR . '/NFactory.php';
require_once NETTE_DIR . '/NDebug.php';
require_once NETTE_DIR . '/NPage.php';
require_once NETTE_DIR . '/NRouter.php';


/**
 * Autoloading
 */
function __autoload($class)
{
    Nette::registry('autoload')->load($class);
}


/**
 * error handling configuration
 * TODO!
 */
error_reporting(E_ALL | E_STRICT);
NDebug::handleErrors();






/**
 * Dispatch forwarding
 */
class NDispatchException extends Exception
{
    /** @var NPage */
    public $next;

    /** @var bool */
    public $isFinal;

    public function __construct(NPage $page, $isFinal=FALSE)
    {
        $this->next = $page;
        $this->isFinal = $isFinal;
        parent::__construct();
    }

}




final class Nette
{
    const
        VERSION = '0.1',
        OFF     = 'OFF',
        DEBUG   = 'DEBUG',
        NORMAL  = 'NORMAL',
        PERFORMANCE = 'PERF';

    const MAX_LOOP = 20;


    /** @var NFactory */
    static private $factory;

    /** @var array  storage for shared objects */
    static private $registry = array();

    /** @var SplObjectStorage  */
    static private $observers;

    /** @var NPage */
    static private $page;

    /** @var string */
    static public $errorPage = 'NDefaultErrorPage';

    /** @var string */
    static public $redirectPage = 'NDefaultRedirectPage';




    /**
     * Static class
     */
    private function __construct()
    {}


    /**
     * Dispatch an HTTP request to a page & action
     */
    static public function run()
    {
        NHttpResponse::setHeader('X-Powered-By: Nette Framework', TRUE);

        // check Nette mode
        if (NETTE_MODE === self::OFF) {
            NHttpResponse::setCode(NHttpResponse::S503_SERVICE_UNAVAILABLE);
            die("Server is currently off.");
        }

        // check HTTP method
        $method = NHttpRequest::getMethod();
        $allowed = array('GET'=>1, 'POST'=>1, 'HEAD'=>1);
        if (!isset($allowed[$method])) {
            NHttpResponse::setCode(501); // 501 Not Implemented
            NHttpResponse::setHeader('Allow: ' . implode(', ', array_keys($allowed)), TRUE);
            die("Method $method not allowed.");
        }

        self::notify('begin');

        // dispatching
        $loops = 0;
        $final = FALSE;
        $page = NULL;
        do {
            if (++$loops > self::MAX_LOOP)
                throw new NetteException('Infinite loop.');

            try {
                // first round => route
                if (!$page) {
                    $page = self::registry('router')->route();

                    if (!($page instanceof NPage))
                        throw new NetteException('No route');

                    if (!$page->isReachable())
                        throw new NetteException('Bad route');
                }

                self::$page = $page;

                // initializate & validate params
                $page->initialize();

                // authorization
                $page->authorize();

                // execute
                $page->executeAction();

                // rendering
                if ($method !== 'HEAD') {
                    $page->render();
                    NHttpResponse::fixIE();
                }

                // dispatching finished
                break;

            } catch (Exception $e) {

                if ($final) throw $e;

                if ($e instanceof NDispatchException) {
                    // not real exception, just forwarding dispatcher
                    $page = $e->next;
                    $final = $e->isFinal;
                } else {
                    // real exception
                    $final = TRUE;
                    if ($page) $page = $page->link(self::$errorPage);
                    else $page = new self::$errorPage;
                    $page->code = 400;
                    $page->message = 'Exception raised';
                    $page->exception = $e;
                }
            }

        } while (1);

        self::notify('end');
    }


    static public function getPage()
    {
        return self::$page;
    }


    static public function link($class, $params=NULL, $action=NULL)
    {
        return self::$page->link($class, $params, $action);
    }


    static public function attach($observer)
    {
        if (!self::$observers)
            self::$observers = new SplObjectStorage();

        self::$observers->attach($observer);
    }


    static public function detach($observer)
    {
        if (self::$observers)
            self::$observers->detach($observer);
    }


    static private function notify($event)
    {
        if (self::$observers)
            foreach (self::$observers as $obj)
                $obj->update('Nette', $event);
    }


    /**
     * Registers a shared object
     * @param string  name for the object
     * @param object  object to register
     * @throws NetteException
     * @return object
     */
    static public function register($name, $obj)
    {
        if (!is_string($name) || !is_object($obj))
            throw new NetteException("First argument must be string and second object.");

        if (isset(self::$registry[$name]))
            throw new NetteException("Object '$name' is already registered.");

        return self::$registry[$name] = $obj;
    }


    /**
     * Retrieves a registered shared object
     * @param string  name for the object.
     * @throws NetteException
     * @return object
     */
    static public function registry($name)
    {
        if (!is_string($name))
            throw new NetteException("Name must be string.");

        if (!isset(self::$registry[$name])) {
            $factory = self::getFactory();
            if (!method_exists($factory, $name))
                throw new NetteException("No object '$name' was registered.");

            return self::$registry[$name] = $factory->$name();
        }

        return self::$registry[$name];
    }


    /**
     * @param string
     * @return boolean
     */
    static public function isRegistered($name)
    {
        return isset(self::$registry[$name]);
    }


    /**
     * @param string
     * @param mixed  additional arguments
     * @return object
     */
    static public function factory($name)
    {
        if (!is_string($name))
            throw new NetteException("Name must be string.");

        $factory = self::getFactory();
        if (!method_exists($factory, $name))
            throw new NetteException("No factory method '$name' exists.");

        $args = func_get_args();
        unset($args[0]);
        return call_user_func_array(array($factory, $name), $args);
    }


    /**
     * @return NFactory
     */
    static private function getFactory()
    {
        if (!self::$factory) {
            $class = defined('NETTE_FACTORY') ? NETTE_FACTORY : 'NFactory';
            self::$factory = new $class;
            if (!self::$factory instanceof NFactory)
                throw new NetteException('Factory is not NFactory');
        }
        return self::$factory;
    }


    /**
     * Nette promo
     * @return string
     */
    static public function nettePowered()
    {
        return '<a href="http://nette.texy.info/" title="Nette - the most innovative PHP framework"><img src="http://texy.info/images/nette-powered.gif" alt="Powered by Nette Framework" /></a>';
    }

}
