<?php

/**
 * This file is part of the Nette Framework (http://nette.texy.info/)
 *
 * Copyright (c) 2005-2007 David Grudl aka -dgx- <dave@dgx.cz>
 *
 * @version  $Revision: 54 $ $Date: 2007-06-12 13:19:17 +0200 (út, 12 VI 2007) $
 * @package  Nette
 */



final class NTools
{

    /**
     * Static class pattern: forbid "new"
     */
    final private function __construct()
    {}


    /**
     * Runs script in a limited scope
     * @param string  file to include.
     * @param bool    include or include_once?
     * @return mixed The return value of the included file.
     */
    static public function loadScript($file, $once=FALSE)
    {
        unset($file, $once);
        if (func_get_arg(1))
            return include_once func_get_arg(0);
        else
            return include func_get_arg(0);
    }



    /**
     * Generates a unique ID
     * @return string
     */
    public static function uniqid()
    {
        static $entropy = 0;
        $entropy++;
        $id = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $id = md5(uniqid($id . $entropy, TRUE));
        $id = base_convert($id, 16, 36);
        return $id;
    }



    /**
     * Checks if the string is valid for the UTF-8 encoding
     * @param string
     * @return string
     */
    public static function checkUTF($s)
    {
        if (preg_match('##u', $s)) {
            return $s;
        }
        return FALSE;
    }



    /**
     * json_encode for strings
     */
    public static function jsEscape($s)
    {
        if (function_exists('json_encode')) { // since PHP 5.2.0
            return json_encode($s);
        }

        $s = strtr($s, "\0", ' '); // fix #40915 for PHP < 5.2.2
        return addcslashes($s, "\x8..\xA\xC\xD\"\\/");
    }



    private static $catchedError;
    private static $oldHandler;

    public static function __catchErrorHandler($errno, $errstr)
    {
        self::$catchedError = $errstr;
    }



    public static function tryError()
    {
        self::$catchedError = NULL;
        // setup catchErrorHandler
        self::$oldHandler = set_error_handler(array(__CLASS__, '__catchErrorHandler'), E_ALL);
    }



    public static function catchError()
    {
        $error = self::$catchedError;
        self::$catchedError = NULL;

        // restore old error handler
        if (self::$oldHandler) {
            set_error_handler(self::$oldHandler);
        } else {
            restore_error_handler();
        }

        return $error;
    }
}



function need(&$key, $default = NULL)
{
    if ($key === NULL) return $key = $default;
    return $key;
}
