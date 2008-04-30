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



/**
 * URI Syntax (RFC 3986)
 *
 *  http://user:pass@nettephp.com:8042/basePath/script.php?name=ferret#nose
 *  \__/^^^\_________________________/\__________________/^\_________/^\__/
 *   |                 |                       |                |       |
 * scheme          authority                 path             query  fragment
 *
 * authority:   [user[:pass]@]host[:port]
 * hostUri:     http://user:pass@nettephp.com:8042
 * basePath:    /basePath  (everything before relative uri not including the script name)
 * baseUri:     http://user:pass@nettephp.com:8042/basePath
 * baseScript:  /basePath/script.php  (URI-path of the request with the script name)
 * relativeUri: /script.php
 *
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Web
 * @version    $Revision$ $Date$
 */
class Uri extends /*Nette::*/Object
{
    /** @var string */
    public $scheme = '';

    /** @var string */
    public $user = '';

    /** @var string */
    public $pass = '';

    /** @var string */
    public $host = '';

    /** @var int */
    public $port = 0;

    /** @var string */
    public $path = '';

    /** @var string */
    public $query = '';

    /** @var string */
    public $fragment = '';

    /** @var string */
    public $baseScript = '';

    /** @var string */
    public $basePath = '';



    /**
     * @param string  URL
     */
    public function __construct($uri = NULL)
    {
        if ($uri !== NULL) {
            foreach (parse_url($uri) as $key => $val) {
                $this->$key = $val;
            }

            if (!$this->port) {
                if ($this->scheme === 'http') {
                    $this->port = 80;
                } if ($this->scheme === 'https') {
                    $this->port = 443;
                }
            }
        }
    }



    /**
     * Returns the entire URI including query string and fragment.
     * @return string
     */
    public function getAbsoluteUri()
    {
        return $this->scheme . '://' . $this->getAuthority() . $this->path
            . ($this->query == '' ? '' : '?' . $this->query)
            . ($this->fragment == '' ? '' : '#' . $this->fragment);
    }



    /**
     * Returns the [user[:pass]@]host[:port] part of URI
     * @return string
     */
    public function getAuthority()
    {
        $authority = $this->host;
        if ($this->port && ($this->scheme !== 'https' || $this->port != 443) && ($this->scheme !== 'http' || $this->port != 80)) {
            $authority .= ':' . $this->port;
        }

        if ($this->user != '') {
            $authority = $this->user . ($this->pass == '' ? '' : ':' . $this->pass) . '@' . $authority;
        }
        return $authority;
    }



    /**
     * Returns the scheme and authority part of URI
     * @return string
     */
    public function getHostUri()
    {
        return $this->scheme . '://' . $this->getAuthority();
    }



    /**
     * Returns the base-URI
     * @return string
     */
    public function getBaseUri()
    {
        return $this->scheme . '://' . $this->getAuthority() . $this->basePath;
    }



    /**
     * Returns the relative-URI
     * @return string
     */
    public function getRelativeUri()
    {
        return substr($this->path, strlen($this->basePath));
    }



    /**
     * URI comparsion (this object must be in canonical form).
     * @param  string
     * @return bool
     */
    public function isEqual($uri)
    {
        // compare host + path
        $part = self::unescape(strtok($uri, '?#'));
        if (strncmp($part, '//', 2) === 0) { // absolute URI without scheme
            if ($part !== '//' . $this->getAuthority() . $this->path) return FALSE;

        } elseif (strncmp($part, '/', 1) === 0) { // absolute path
            if ($part !== $this->path) return FALSE;

        } else {
            if ($part !== $this->scheme . '://' . $this->getAuthority() . $this->path) return FALSE;
        }

        // compare query strings
        $part = strtok('?#');
        if ($part !== FALSE) {
            $tmp = explode('&', self::unescape($part));
            sort($tmp);
            if (implode('&', $tmp) !== $this->query) {
                return FALSE;
            }
        }

        return TRUE;
    }



    /**
     * Transform to canonical form.
     * @return void
     */
    public function canonicalize()
    {
        $this->path = $this->path == '' ? '/' : self::unescape($this->path);

        $this->host = strtolower($this->host);

        if ($this->query !== '') {
            $tmp = explode('&', self::unescape($this->query));
            sort($tmp);
            $this->query = implode('&', $tmp);
        }
    }



    /**
     * Similar to rawurldecode, but preserve reserved chars encoded.
     * @param  string to decode
     * @return string
     */
    public static function unescape($s)
    {
        // reserved (@see RFC 2396) = ";" | "/" | "?" | ":" | "@" | "&" | "=" | "+" | "$" | ","
        // within a path segment, the characters "/", ";", "=", "?" are reserved
        // within a query component, the characters ";", "/", "?", ":", "@", "&", "=", "+", ",", "$" are reserved.
        return rawurldecode(preg_replace('#%(40|3[ABDF]|2[46BCF])#i', '%25$1', $s));
    }

}
