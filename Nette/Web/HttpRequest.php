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
 * @package    Nette::Web
 */

/*namespace Nette::Web;*/


require_once dirname(__FILE__) . '/../Object.php';

require_once dirname(__FILE__) . '/../Web/IHttpRequest.php';



/**
 * HttpRequest provides access scheme for request sent via HTTP.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Web
 * @version    $Revision$ $Date$
 */
class HttpRequest extends /*Nette::*/Object implements IHttpRequest
{
    /** @var array  @see self::getHeader() */
    private $headers;

    /** @var array  @see self::isLocal() */
    private $isLocal;

    /** @var string  @see self::getRawUrl() */
    private $rawUrl;

    /** @var array  @see self::isEqual() */
    private $normalizedUrl;

    /** @var string  @see self::getBaseScript() */
    private $baseScript;

    /** @var string  @see self::getBaseUrl() */
    private $baseUrl;

    /** @var string  @see self::getBasePath() */
    private $basePath;



    /**
     * Returns HTTP request method (GET, POST, HEAD, PUT, ...).
     * @return string
     */
    public function getMethod()
    {
        return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : NULL;
    }



    /**
     * Is HTTP method GET?
     * @return boolean
     */
    public function isGet()
    {
        return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET';
    }



    /**
     * Is HTTP method POST?
     * @return boolean
     */
    public function isPost()
    {
        return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
    }



    /**
     * Is HTTP method HEAD?
     * @return boolean
     */
    public function isHead()
    {
        return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'HEAD';
    }



    /**
     * Returns URL scheme name (http or https).
     * @return string
     */
    public function getScheme()
    {
        return $this->isSecured() ? 'https' : 'http';
    }



    /**
     * Returns host name with optional port.
     * @return string
     */
    public function getHost()
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }

        if (isset($_SERVER['SERVER_NAME'])) {
            return $_SERVER['SERVER_NAME'];
        }

        return NULL;
    }



    /**
     * Changes the REQUEST URI-path (for cases when autodetection fails).
     * @param  string
     * @return void
     */
    public function setRawUrl($value)
    {
        if (!is_string($value)) {
            return;
        }

        $this->rawUrl = '/' . ltrim($value, '/');
        $this->normalizedUrl = NULL;

        $_GET = array();
        if (($pos = strpos($value, '?')) !== FALSE) {
            parse_str(substr($values, $pos + 1), $_GET);
            self::fuckingQuotes(array(&$_GET));
        }
    }



    /**
     * Returns the REQUEST URI-path taking into account platform differences.
     * @return string
     */
    public function getRawUrl()
    {
        if ($this->rawUrl === NULL) {
            // request URI autodetection
            if (isset($_SERVER['REQUEST_URI'])) { // Apache, IIS 6.0
                $this->rawUrl = $_SERVER['REQUEST_URI'];
            } elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0 (PHP as CGI ?)
                $this->rawUrl = $_SERVER['ORIG_PATH_INFO'];
                if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '') {
                    $this->rawUrl .= '?' . $_SERVER['QUERY_STRING'];
                }
            } else {
                $this->rawUrl = ''; // can't detect
            }
        }

        return $this->rawUrl;
    }



    /**
     * Returns the full URL.
     * @return string
     */
    public function getUrl()
    {
        return $this->getScheme() . '://' . $this->getHost() . $this->getRawUrl();
    }



    /**
     * Returns the URL-path.
     * @return string
     */
    public function getUrlPath()
    {
        // TODO: chybi v interface
        $path = strtok($_SERVER['REQUEST_URI'], '?');
        $path = rawurldecode(preg_replace('#%(40|3[ABDF]|2[46BCF])#i', '%25$1', $path)); // decode %XX (@see RFC 2616 - 3.2.3 URI Comparison
        return $path;
    }



    /**
     * Detects base URL-path and script path of the request.
     * @return void
     */
    public function detectPaths()
    {
        $filename = basename($_SERVER['SCRIPT_FILENAME']);

        // inspired by Zend Framework (c) Zend Technologies USA Inc. (http://www.zend.com), new BSD license
        if (basename($_SERVER['SCRIPT_NAME']) === $filename) {
            $baseScript = $_SERVER['SCRIPT_NAME'];
        } elseif (basename($_SERVER['PHP_SELF']) === $filename) {
            $baseScript = $_SERVER['PHP_SELF'];
        } elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $filename) {
            $baseScript = $_SERVER['ORIG_SCRIPT_NAME']; // 1and1 shared hosting compatibility
        } else {
            // Backtrack up the script_filename to find the portion matching
            // php_self
            $path = $_SERVER['PHP_SELF'];
            $segs = explode('/', trim($_SERVER['SCRIPT_FILENAME'], '/'));
            $segs = array_reverse($segs);
            $index = 0;
            $last = count($segs);
            $baseScript = '';
            do {
                $seg = $segs[$index];
                $baseScript = '/' . $seg . $baseScript;
                ++$index;
            } while (($last > $index) && (FALSE !== ($pos = strpos($path, $baseScript))) && (0 != $pos));
        }

        // Does the baseScript have anything in common with the request_uri?
        $requestUrl = $this->getRawUrl();

        // do not use dirinfo!
        $basePath = substr($baseScript, 0, strrpos($baseScript, '/') + 1);

        if (0 === strpos($requestUrl, $baseScript)) {
            // full $baseScript matches
            $this->baseScript = $baseScript;
            $this->basePath = $basePath;
            return;
        }


        if (0 === strpos($requestUrl, $basePath)) {
            // directory portion of $baseScript matches
            $this->baseScript = $this->basePath = $basePath;
            return;
        }

        if (strpos($requestUrl, basename($baseScript)) === FALSE) {
            // no match whatsoever; set it blank
            $this->baseScript = '';
            $this->basePath = '/';
            return;
        }

        // If using mod_rewrite or ISAPI_Rewrite strip the script filename
        // out of baseScript. $pos !== 0 makes sure it is not matching a value
        // from PATH_INFO or QUERY_STRING
        if ((strlen($requestUrl) >= strlen($baseScript))
            && ((false !== ($pos = strpos($requestUrl, $baseScript))) && ($pos !== 0)))
        {
            $baseScript = substr($requestUrl, 0, $pos + strlen($baseScript));
            // do not use dirinfo!
            $basePath = substr($baseScript, 0, strrpos($baseScript, '/') + 1);
        }

        $this->baseScript = rtrim($baseScript, '/');
        $this->basePath = $basePath;
    }



    /**
     * Returns the URL-path for the root of your site.
     *
     * @return string
     */
    public function getBasePath()
    {
        if ($this->basePath === NULL) {
            $this->detectPaths();
        }
        return $this->basePath;
    }



    /**
     * Returns the absolute URL for the root of your site.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        if ($this->basePath === NULL) {
            $this->detectPaths();
        }
        return $this->getScheme() . '://' . $this->getHost() . $this->basePath;
    }



    /**
     * Returns the URL-path of the request with the script name.
     *
     * @return string
     */
    public function getBaseScript()
    {
        if ($this->baseScript === NULL) {
            $this->detectPaths();
        }
        return $this->baseScript;
    }



    /**
     * Changes the URL-path of the request.
     * @param  string
     * @return void
     */
    public function setBaseUrl($value)
    {
        // TODO: predelat
        if (!is_string($value)) {
            return;
        }
        $value = '/' . ltrim($value, '/');
        $this->baseScript = $value;
        // do not use dirinfo!
        $this->basePath = substr($value, 0, strrpos($value, '/') + 1);
    }



    /**
     * Returns variable provided to the script via URL query ($_GET).
     * If no $key is passed, returns the entire array.
     *
     * @param  string
     * @param  mixed  default value to use if key not found
     * @return mixed
     */
    public function getQuery($key = NULL, $default = NULL)
    {
        if ($key === NULL) {
            return $_GET;

        } elseif (isset($_GET[$key])) {
            return $_GET[$key];

        } else {
            return $default;
        }
    }



    /**
     * Returns variable provided to the script via POST method ($_POST).
     * If no $key is passed, returns the entire array.
     *
     * @param  string
     * @param  mixed  default value to use if key not found
     * @return mixed
     */
    public function getPost($key = NULL, $default = NULL)
    {
        if ($key === NULL) {
            return $_POST;

        } elseif (isset($_POST[$key])) {
            return $_POST[$key];

        } else {
            return $default;
        }
    }



    /**
     * Returns uploaded file.
     * If no $key is passed, returns the entire array.
     *
     * @param  string
     * @return array|NULL
     */
    public function getFile($key = NULL)
    {
        if ($key === NULL) {
            return $_FILES;

        } elseif (isset($_FILES[$key])) {
            return $_FILES[$key];

        } else {
            return NULL;
        }
    }



    /**
     * Returns HTTP POST data in raw format (only for "application/x-www-form-urlencoded").
     * @return string
     */
    public function getPostRaw()
    {
        return file_get_contents('php://input');
    }



    /**
     * Returns variable provided to the script via HTTP cookies.
     * If no $key is passed, returns the entire array.
     *
     * @param  string
     * @param  mixed  default value to use if key not found
     * @return mixed
     */
    public function getCookie($key = NULL, $default = NULL)
    {
        if ($key === NULL) {
            return $_COOKIE;

        } elseif (isset($_COOKIE[$key])) {
            return $_COOKIE[$key];

        } else {
            return $default;
        }
    }



    /**
     * Return the value of the HTTP header. Pass the header name as the.
     * plain, HTTP-specified header name. Ex.: Ask for 'Accept' to get the
     * Accept header, 'Accept-Encoding' to get the Accept-Encoding header.
     *
     * @param  string
     * @param  mixed
     * @return array|string|NULL  list or single HTTP request header
     */
    public function getHeader($key = NULL, $default = NULL)
    {
        if ($this->headers === NULL) {
            // lazy initialization
            if (function_exists('apache_request_headers')) {
                $this->headers = array_change_key_case(apache_request_headers(), CASE_LOWER);
            } else {
                $this->headers = array();
                foreach ($_SERVER as $k => $v) {
                    if (strncmp($k, 'HTTP_', 5) == 0) {
                        $this->headers[ strtr(strtolower(substr($k, 5)), '_', '-') ] = $v;
                    }
                }
            }
        }

        if ($key === NULL) {
            return $this->headers;
        }

        $key = strtolower($key);
        if (isset($this->headers[$key])) {
            return $this->headers[$key];
        }

        return $default;
    }



    /**
     * Is the request is sent via secure channel (https).
     * @return boolean
     */
    public function isSecured()
    {
        return isset($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off');
    }



    /**
     * Is Ajax request?
     * @return boolean
     */
    public function isAjax()
    {
        return $this->isPost() && $this->getHeader('X-Requested-With') === 'XMLHttpRequest';
    }



    /**
     * Is server is running on local host?
     * @return boolean
     */
    public function isLocal()
    {
        if ($this->isLocal === NULL) {
            $this->isLocal = FALSE;
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $oct = explode('.', $_SERVER['REMOTE_ADDR']);
                $this->isLocal = (count($oct) === 4) && ($oct[0] === '10' || $oct[0] === '127' || ($oct[0] === '171' && $oct[1] > 15 && $oct[1] < 32)
                    || ($oct[0] === '169' && $oct[1] === '254') || ($oct[0] === '192' && $oct[1] === '168'));
            }
        }
        return $this->isLocal;
    }



    /**
     * Parse Accept-Language header and returns prefered language.
     * @param  array   Supported languages
     * @return string
     */
    public function detectLanguage(/*array*/ $langs)
    {
        if (!isset($this->headers['accept-language'])) {
            return NULL;
        }

        $s = strtolower($this->headers['accept-language']);  // case insensitive
        $s = strtr($s, '_', '-');  // cs_CZ means cs-CZ
        rsort($langs);             // first more specific
        preg_match_all('#('.implode('|', $langs).')(?:-[^\s,;=]+)?\s*(?:;\s*q=([0-9.]+))?#', $s, $matches);

        if (!$matches[0]) {
            return NULL;
        }

        $max = 0;
        $lang = NULL;
        foreach ($matches[1] as $key => $value) {
            $q = $matches[2][$key] === '' ? 1.0 : (float) $matches[2][$key];
            if ($q > $max) {
                $max = $q; $lang = $value;
            }
        }

        return $lang;
    }



    /**
     * Similar to rawurldecode, but preserve reserved chars encoded.
     * @param  string to decode
     * @return string
     */
    public static function urldecode($s)
    {
        // reserved (@see RFC 2396) = ";" | "/" | "?" | ":" | "@" | "&" | "=" | "+" | "$" | ","
        // within a path segment, the characters "/", ";", "=", "?" are reserved
        // within a query component, the characters ";", "/", "?", ":", "@", "&", "=", "+", ",", "$" are reserved.
        return rawurldecode(preg_replace('#%(40|3[ABDF]|2[46BCF])#i', '%25$1', $s));
    }



    /**
     * Generates hash from all used IP & host names from request headers.
     * @return string
     */
    public function ipHash()
    {
        static $vars = array(
            'REMOTE_ADDR', 'HTTP_FROM', 'HTTP_CLIENT_IP', 'HTTP_VIA', 'HTTP_COMING_FROM', 'HTTP_X_COMING_FROM', 'HTTP_PROXY_CONNECTION',
            'HTTP_PROXY', 'HTTP_X_PROXY', 'HTTP_X_FORWARDED_FOR', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'HTTP_X_FORWARDED',
        );

        $ip = array();
        foreach ($vars as $var) {
            if (isset($_SERVER[$var])) {
                $ip[] = substr($_SERVER[$var], 0, 20);
            }
        }

        return sha1(implode('|', $ip));
    }



    /**
     * URL comparing.
     * @param  string
     * @return bool
     */
    public function isEqual($url)
    {
        if ($this->normalizedUrl === NULL) {
            $parts = explode('?', $this->getRawUrl(), 2);
            $this->normalizedUrl['path'] = self::urldecode($parts[0]);
            $this->normalizedUrl['host'] = strtolower($this->getHost());

            if (isset($parts[1])) {
                $tmp = explode('&', $parts[1]);
                sort($tmp);
                $this->normalizedUrl['query'] = implode('&', $tmp);
            } else {
                $this->normalizedUrl['query'] = '';
            }
        }

        if (strncmp($url, '//', 2) === 0) { // absolute URI without scheme
            $origUrl = '//' . $this->normalizedUrl['host'] . $this->normalizedUrl['path'];

        } elseif (strncmp($url, '/', 1) === 0) { // absolute path
            $origUrl = $this->normalizedUrl['path'];

        } else {
            $origUrl = $this->getScheme() . '://' . $this->normalizedUrl['host'] . $this->normalizedUrl['path'];
        }

        // first test
        $parts = explode('?', $url, 2);
        if (self::urldecode($parts[0]) !== $origUrl) {
            return FALSE;
        }

        // compare query strings
        if (isset($parts[1])) {
            $tmp = explode('#', $parts[1], 2); // but there shouldn't be any fragment...
            $tmp = explode('&', $tmp[0]);
            sort($tmp);
            if (implode('&', $tmp) !== $this->normalizedUrl['query']) {
                return FALSE;
            }
        }

        // URIs are the same
        return TRUE;
    }



    /**
     * Magic quotes remover.
     * @param  array
     * @return void
     */
    public static function fuckingQuotes($list)
    {
        if (get_magic_quotes_gpc()) {
            while (list($k, $v) = each($list)) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $foo) $list[] = & $list[$k][$k2];
                } else {
                    $list[$k] = stripSlashes($v);
                }
            }
        }
    }

}



// init: HttpRequest::fuckingQuotes(array(&$_GET, &$_POST, &$_COOKIE, &$_FILES));
