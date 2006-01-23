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
 * Example:
 *
 *  $this->relPath = strtolower($this->relPath);
 *  $this->addRoute('{lang cs|en|de|pl}/{file}.texy',     'ShowSourcePage');
 *  $this->addRoute('{lang cs|en|de|pl}/try/{bookmark?}', 'TryPage');
 *  $this->addRoute('{lang cs|en|de|pl}/client',          'ClientPage');
 *  $this->addRoute('{lang cs|en|de|pl}/login',           'LoginPage');
 *  $this->addRoute('{lang cs|en|de|pl}/{file?}',         'InfoPage');
 */

class NRewriteRouter extends NRouter
{
    /** @var array */
    private $routes = array();
    private $routesC = array();

    /** @var array */
    protected $params;


    public function addRoute($mask, $class)
    {
        $this->routes[] = array($class, $mask);

        if (!isset($this->routesC[$class])) $this->routesC[$class] = $mask;
    }



    static private function cbA($m)
    {
        if (isset($m[3])) return preg_quote($m[3], '#');
/*        if ($m[2] == '') return '(?P<' . $m[1] . '>(?>[^/]+?))';
        if ($m[2] == '?') return '(?P<' . $m[1] . '>(?>[^/]+?))?'; */
        if ($m[2] == '') return '(?P<' . $m[1] . '>[^/]+?)';
        if ($m[2] == '?') return '(?P<' . $m[1] . '>[^/]+?)?';
        return '(?P<' . $m[1] . '>(?>' . $m[2] . '))';
    }


    /**
     * Maps HTTP request to a NPage object
     * @return NPage
     */
    public function route()
    {
        foreach ($this->routes as $route) {
            list($class, $mask) = $route;
            // convert to regular expression
            $mask = preg_replace_callback('#\{([a-z0-9_]+) ?(.*?)\}|([^{]+)#', array(__CLASS__, 'cbA'), $mask);
            // matches?
            if (preg_match('#^' . $mask . '/*$#', $this->relPath . '/', $matches)) {

                // combine arrays
                foreach ($matches as $key => $val)
                    if (is_string($key)) $this->params[$key] = $val;

                $action = isset($this->params['action']) ? $this->params['action'] : NULL;
                unset($this->params['action']);
                return $this->factoryPage($class, $this->params, $action);
            }
        }
    }



    private $_params;
    private $_class;

    private function cbB($m)
    {
        if (!isset($this->_params[$m[1]])) {
            if ($m[2] == '?') return '';

            $list = NLink::getParamList($this->_class);
            if (!isset($list[$m[1]])) {
                throw new NetteException("Unknown parameter '$m[1]'.");
            }
            return $list[$m[1]]['def'];
        }

        $p = $this->_params[$m[1]];
        unset($this->_params[$m[1]]);
        if ($p === '') {
            if ($m[2] == '?') return '';
            throw new NetteException("Empty parameter '$m[1]' not allowed in URL.");
        }
        return $p;
    }



    /**
     * Maps NPage object to absolute URI or path
     * @see RFC 2396 (http://www.ietf.org/rfc/rfc2396.txt)
     * @param NPage destination
     * @return string|FALSE
     */
    public function generateUri(NPage $page)
    {
        // debugger support - really needed?
        //if (strpos($_SERVER['REQUEST_URI'], 'DBGSESSID')) $params['DBGSESSID'] = NHttpRequest::get('DBGSESSID');

        $class = get_class($page);
        if (!isset($this->routesC[$class])) {
            //trigger_error("Can't make link to '$pageClass'.", E_USER_WARNING);
            return FALSE;
        }

        $mask = $this->routesC[$class];
        $this->_params = $page->getParams();
        $this->_params['action'] = $page->getAction();
        $this->_class = $class;
        $uri = preg_replace_callback('#\{([a-z0-9_]+) ?(.*?)\}#', array($this, 'cbB'), $mask);
        $uri = $this->getRoot() . $uri;

        // remove NULLs
        foreach ($this->_params as $k => $v) if ($v===NULL) unset($this->_params[$k]);

        if ($this->_params) {
            $s = http_build_query($this->_params);
            if ($s != NULL) $uri .= '?' . $s;
        }
        return $uri;
    }

}
