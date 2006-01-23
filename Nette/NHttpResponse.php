<?php

/**
 * This file is part of the Nette Framework (http://nette.texy.info/)
 *
 * Copyright (c) 2005-2007 David Grudl aka -dgx- <dave@dgx.cz>
 *
 * @version  $Revision: 51 $ $Date: 2007-04-11 20:34:53 +0200 (st, 11 IV 2007) $
 * @package  Nette
 */


/**
 * NHttpResponse
 * @static
 */
final class NHttpResponse
{
    /** cookie expirations */
    const PERMANENT = 2116333333; // 23.1.2037
    const WINDOW    = 0;   // end of session, when the browser closes

    // HTTP 1.1 response codes
    const
        S200_OK = 200,
        S301_MOVED_PERMANENTLY = 301,
        S302_FOUND = 302,
        S303_SEE_OTHER = 303,
        S303_POST_GET = 303,
        S307_TEMPORARY_REDIRECT= 307,
        S400_BAD_REQUEST = 400,
        S401_UNAUTHORIZED = 401,
        S403_FORBIDDEN = 403,
        S404_NOT_FOUND = 404,
        S410_GONE = 410,
        S500_INTERNAL_SERVER_ERROR = 500,
        S503_SERVICE_UNAVAILABLE = 503;


    /** @var string The domain in which the cookie will be available */
    static public $cookieDomain = '';

    /** @var string The path in which the cookie will be available */
    static public $cookiePath = '/';


    static private $code = NULL;

    static private $allowed = array(
        200=>1, 201=>1, 202=>1, 203=>1, 204=>1, 205=>1, 206=>1,
        300=>1, 301=>1, 302=>1, 303=>1, 304=>1, 307=>1,
        400=>1, 401=>1, 403=>1, 404=>1, 406=>1, 408=>1, 410=>1, 412=>1, 415=>1, 416=>1,
        500=>1, 501=>1, 503=>1, 505=>1
    );




    /**
     * Static class
     */
    private function __construct()
    {}


    /**
     * Sets HTTP response code
     * @param int
     * @return void
     */
    static public function setCode($code)
    {
        $code = (int) $code;

        if (!isset(self::$allowed[$code]))
            throw new NetteException("Bad HTTP response '$code'.");

        self::$code = $code;
        header('HTTP/1.1 ' . $code, TRUE, $code);
    }


    /**
     * Returns HTTP response code
     * @return int
     */
    static public function getCode()
    {
        return self::$code;
    }


    static public function setHeader($header, $replace = FALSE)
    {
        // prevent header injection
        $header = str_replace(array("\n", "\r"), '', $header);
        header($header, $replace);
    }


    static public function setContentType($type='text/html', $charset='ISO-8859-1')
    {
        self::setHeader('Content-type: ' . $type . ($charset ? '; charset=' . $charset : ''), TRUE);
    }


    /**
     * Returns HTTP valid date format
     * @param int  timestamp
     * @return string
     */
    static public function date($time=NULL)
    {
        if ($time === NULL) $time = time();
        return gmdate('D, d M Y H:i:s \G\M\T', $time);
    }


    static public function expire($time)
    {
        if ($time > 0) {
            header('Cache-Control: max-age=' . (int) $time . ',must-revalidate', TRUE);
            header('Expires: ' . self::date(time() + $time), TRUE);
        } else {
            // no cache
            header('Cache-Control: s-maxage=0, max-age=0, must-revalidate', TRUE);
            header('Pragma: no-cache', TRUE);
            header('Expires: Mon, 23 Jan 1978 10:00:00 GMT', TRUE);
        }
    }


    static public function enableCompression()
    {
        // test
        $ok = ob_gzhandler('', PHP_OUTPUT_HANDLER_START);
        if ($ok === FALSE) return FALSE; // not allowed

        if (function_exists('ini_set')) {
            ini_set('zlib.output_compression', 'Off');
            ini_set('zlib.output_compression_level', '6');
        }
        ob_start('ob_gzhandler');
        return TRUE;
    }



    /**
     * Sends garbage for IE
     */
    static public function fixIE()
    {
        $codes = array(400=>1,403=>1,404=>1,405=>1,406=>1,408=>1,409=>1,410=>1,500=>1,501=>1,505=>1);
        if (isset($codes[self::$code])) {
            $s = " \t\r\n";
            for ($i=2e3; $i; $i--) echo $s{rand(0, 3)};
        }
    }



    /**
     * Defines a new cookie
     * @param string name of the cookie.
     * @param string value
     * @param int expiration as unix timestamp
     * @return void
     */
    static public function setCookie($name, $value, $expire)
    {
        setCookie($name, $value, $expire, self::$cookiePath, self::$cookieDomain . '; httponly');
    }


    /**
     * Deletes a cookie
     * @param string name of the cookie.
     * @param bool delete cookie although this cookie doesn't exists?
     * @return void
     */
    static public function deleteCookie($name, $force=TRUE)
    {
        if (!$force && !isset($_COOKIE[$name])) return;

        setCookie($name, FALSE, 0, self::$cookiePath, self::$cookieDomain . '; httponly');
    }

}
