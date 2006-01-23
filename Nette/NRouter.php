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
 * The bidirectional router is responsible for mapping
 * HTTP request to a NPage object for dispatch and vice-versa.
 */
abstract class NRouter extends NObject
{
    /** @var string */
    private $root = '/';

    /** @var string */
    protected $relPath;

    /** @var array */
    protected $params;


    public function __construct()
    {
        // relative URI for the requested script, without path info and query
        $this->setRoot(NETTE_WWW_URI);

        // params
        $this->params = $_GET;
    }


    /**
     * Maps HTTP request to a NPage object
     * @return NPage
     */
    abstract public function route();


    /**
     * Maps NPage object to absolute URI or path
     * @see RFC 2396 (http://www.ietf.org/rfc/rfc2396.txt)
     * @param NPage destination
     * @return string|FALSE
     */
    abstract public function generateUri(NPage $page);



    /**
     * @param  string
     * @return bool
     */
    public function setRoot($path)
    {
        if (substr($path, -1) !== '/') $path .= '/';
        if ($path{0} != '/') $path = '/' . $path;

        // check root (debug mode only)
        $reqPath = NHttpRequest::getPath();
        if (strncasecmp($path, $reqPath, strlen($path)))
            return FALSE;

        $this->root = $path;
        $this->relPath = (string) substr($reqPath, strlen($path));
        return TRUE;
    }


    /**
     * @return string
     */
    public function getRoot()
    {
        return $this->root;
    }



    /**
     * Initialize the page class instance
     * @throw NetteException
     * @return NPage
     */
    protected function factoryPage($class, $params=NULL, $action=NULL)
    {
        if (!class_exists($class, TRUE))
            throw new NetteException("Unknown class '$class'.");

        if (!is_subclass_of($class, 'NPage'))
            throw new NetteException("Class '$class' is not NPage subclass.");

        return new $class($params, $action);
    }


}
