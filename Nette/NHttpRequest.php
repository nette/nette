<?php

/**
 * This file is part of the Nette Framework (http://nette.texy.info/)
 *
 * Copyright (c) 2005-2007 David Grudl aka -dgx- <dave@dgx.cz>
 *
 * @version  $Revision: 51 $ $Date: 2007-04-11 20:34:53 +0200 (st, 11 IV 2007) $
 * @package  Nette
 */



NHttpRequest::init();

/**
 * NHttpRequest provides access scheme for request sent via HTTP.
 * @static
 */
final class NHttpRequest
{
    private static
        $headers,
        $method,
        $host,
        $path,
        $query,
        $originalPath,
        $originalQuery,
        $isSecured,
        $isLocal;



    /**
     * Static class
     */
    private function __construct()
    {}


    /**
     * class initialization
     */
    static public function init()
    {
        // method
        self::$method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : NULL;

        // http vs. https
        self::$isSecured = isset($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off');

        // host & port | todo: port, server_name check
        self::$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];

        // local host autodetection
        self::$isLocal = isset($_SERVER['SERVER_ADDR']) && ((int) $_SERVER['SERVER_ADDR'] == 127);

        // path & query
        if (isset($_SERVER['REQUEST_URI'])) {
            $tmp = explode('?', $_SERVER['REQUEST_URI'], 2);
            self::$originalPath = self::$path = $tmp[0];

            if (isset($tmp[1])) {
                self::$originalQuery = self::$query = $tmp[1]; // may be empty string

                // query tolerance: replace bad entities
                self::$query = str_replace('&amp;', '&', self::$query);

                // query tolerance: trim last spec. char
                if (strspn(self::$query, '.?),!"\'', -1))
                    self::$query = (string) substr(self::$query, 0, -1);

                // re-parse $_GET?
                if (self::$originalQuery != self::$query)
                    parse_str(self::$query, $_GET);

            } else {
                self::$originalQuery = self::$query = NULL;

                // path tolerance: trim last spec. char
                if (strspn(self::$path, '.?),!"\'', -1))
                    self::$path = (string) substr(self::$path, 0, -1);
            }

            // path tolerance: double slashes
            //self::$path = preg_replace('#/{2,}#', '/', self::$path);

            // decode %XX (@see RFC 2616 - 3.2.3 URI Comparison
            self::$path = self::urldecode(self::$path);

            // path tolerance: remove spaces, %20
            //self::$path = str_replace(' ', '', self::$path);

        } else {
            // todo: any alternative?
            self::$originalQuery = self::$query = NULL;
            self::$originalPath = self::$path = NULL;
        }


        // strip magic quotes
        self::fuckingQuotes(array(&$_GET, &$_POST, &$_COOKIE, &$_FILES));

        $_REQUEST = $_POST + $_GET;
    }


    /**
     * @return string|NULL request method (GET, POST, HEAD, PUT, ...)
     */
    static public function getMethod()
    {
        return self::$method;
    }


    /**
     * @return string HTTP scheme
     */
    static public function getScheme()
    {
        return self::$isSecured ? 'https:' : 'http:';
    }


    /**
     * @return string server host (with optional port)
     */
    static public function getHost()
    {
        return self::$host;
    }


    /**
     * @return string part of that request URI before the question mark
     */
    static public function getPath()
    {
        return self::$path;
    }


    /**
     * @return string|NULL part of that request URI after the question mark (if used)
     */
    static public function getQuery()
    {
        return self::$query;
    }


    /**
     * @return string  request URI
     */
    static public function getURI()
    {
        return (self::$isSecured ? 'https://' : 'http://') . self::$host . self::$path . (self::$query == '' ? '' : '?' . self::$query);
    }


    /**
     * Returns variable provided to the script via URL query ($_GET)
     * @param string variable key
     * @return array|string|NULL
     */
    static public function getParam($key)
    {
        if (isset($_REQUEST[$key])) return $_REQUEST[$key];
        return NULL;
    }


    /**
     * @return string HTTP POST data in raw format (only for "application/x-www-form-urlencoded")
     */
    static public function getPostRaw()
    {
        return file_get_contents('php://input');
    }


    /**
     * Returns variable provided to the script via HTTP cookies
     * @param string variable key
     * @return array|string|NULL
     */
    static public function getCookie($key)
    {
        if (isset($_COOKIE[$key])) return $_COOKIE[$key];
        return NULL;
    }


    /**
     * @param string variable key or NULL for whole list
     * @param string|NULL
     * @return array|string|NULL  list or single HTTP request header
     */
    static public function getHeaders($key=NULL)
    {
        if (self::$headers === NULL) {
            // lazy initialization
            if (function_exists('apache_request_headers')) {
                self::$headers = array_change_key_case(apache_request_headers(), CASE_LOWER);
            } else {
                self::$headers = array();
                foreach ($_SERVER as $k => $v) {
                    if (strncmp($k, 'HTTP_', 5) == 0)
                        self::$headers[ strtr(strtolower(substr($k, 5)), '_', '-') ] = $v;
                }
            }
        }

        if ($key===NULL) return self::$headers;
        if (isset(self::$headers[$key])) return self::$headers[$key];
        return NULL;
    }


    /**
     * @return boolean if the request is sent via secure channel (https)
     */
    static public function isSecured()
    {
        return self::$isSecured;
    }


    /**
     * @return boolean if server is running on local host
     */
    static public function isLocal()
    {
        return self::$isLocal;
    }


    /**
     * Parse Accept-Language header and returns prefered language
     * @param array   Supported languages
     * @return string
     */
    static public function detectLanguage($langs)
    {
        if (!isset(self::$headers['accept-language']))
            return NULL;

        $s = strtolower(self::$headers['accept-language']);  // case insensitive
        $s = strtr($s, '_', '-');  // cs_CZ means cs-CZ
        $langs = func_get_args();
        rsort($langs);             // first more specific
        preg_match_all('#('.implode('|', $langs).')(?:-[^\s,;=]+)?\s*(?:;\s*q=([0-9.]+))?#', $s, $matches);

        if (!$matches[0]) return NULL;

        $max = 0;
        $lang = NULL;
        foreach ($matches[1] as $key => $val) {
            $q = $matches[2][$key] === '' ? 1.0 : (float) $matches[2][$key];
            if ($q > $max) {
                $max = $q; $lang = $val;
            }
        }

        return $lang;
    }


    /**
     * Similar to rawurldecode, but preserve reserved chars encoded
     * @param string to decode
     * @return string
     */
    static public function urldecode($s)
    {
        // reserved (@see RFC 2396) = ";" | "/" | "?" | ":" | "@" | "&" | "=" | "+" | "$" | ","
        return rawurldecode(preg_replace('#%(3B|2F|3F|3A|40|26|3D|2B|24|2C)#i', '%25$1', $s));
    }


    /**
     * @return string all IP & host names from request headers
     */
    static public function ipHash()
    {
        static $vars = array(
            'REMOTE_ADDR', 'HTTP_FROM', 'HTTP_CLIENT_IP', 'HTTP_VIA', 'HTTP_COMING_FROM', 'HTTP_X_COMING_FROM', 'HTTP_PROXY_CONNECTION',
            'HTTP_PROXY', 'HTTP_X_PROXY', 'HTTP_X_FORWARDED_FOR', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'HTTP_X_FORWARDED',
        );

        $ip = array();
        foreach ($vars as $var) if (isset($_SERVER[$var])) $ip[] = substr($_SERVER[$var], 0, 20);

        return implode('|', $ip);
    }



    static public function isEqual($uri)
    {
        if (substr($uri, 0, 2) === '//')  // absoluteURI
            $origUri = '//' . strtolower(self::getHost())
                     . self::urldecode(self::$originalPath);
        elseif (substr($uri, 0, 1) === '/')  // abs_path
            $origUri = self::urldecode(self::$originalPath);
        else  // absoluteURI
            $origUri = self::getScheme() . '//' . strtolower(self::getHost())
                     . self::urldecode(self::$originalPath);

        $parts = explode('?', $uri, 2);
        $uri = self::urldecode($parts[0]);

        // first test
        if ($uri !== $origUri)
            return FALSE;

        // compare query strings
        if (isset($parts[1])) {
            $tmp = explode('#', $parts[1], 2); // but there shouldn't be any fragment...
            $tmp = explode('&', $tmp[0]);
            sort($tmp);
            $uri .= '?' . implode('&', $tmp);
        }

        if (is_string(self::$originalQuery)) {
            $tmp = explode('&', self::$originalQuery);
            sort($tmp);
            $origUri .= '?' . implode('&', $tmp);
        }

        if ($uri !== $origUri)
            return FALSE;

        // URIs are the same
        return TRUE;
    }


    static public function fuckingQuotes($list)
    {
        if (get_magic_quotes_gpc()) {
            while (list($k, $v) = each($list)) {
                if (is_array($v))
                    foreach ($v as $k2 => $foo) $list[] = & $list[$k][$k2];
                else
                    $list[$k] = stripSlashes($v);
            }
        }
    }
}
