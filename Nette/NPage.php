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
 * NPage executes all the logic for the request.
 */
abstract class NPage extends NObject
{
    /** @var string|NULL current action */
    private $action;

    /** @var NForm */
    private $submittedForm = array();

    /** @var array getParamList cache */
    static private $pCache = array();



    /**
     * @param  array
     * @param  string|NULL
     */
    public function __construct($params=NULL, $action=NULL)
    {
        // nonempty string or NULL
        $this->action = is_string($action) && $action !== '' ? $action : NULL;

        // load parameters
        if ($params) {
            $list = self::getParamList(get_class($this));
            foreach ($params as $nm => $val)
            {
                if ($val === NULL) continue;
                if (!isset($list[$nm])) continue;
                if ($list[$nm]['type']) settype($val, $list[$nm]['type']);
                $this->$nm = $val;
            }
        }
    }



    public function initialize()
    {}

    public function authorize()
    {}


    abstract public function render();


    /**
     * Executes action
     * @return void
     */
    public function executeAction()
    {
        if ($this->submittedForm) {
            $name = $this->submittedForm->getName();
            if ($name == NULL) $name = 'Form';
            $by = $this->submittedForm->isSubmitted();
            if (is_string($by)) {
                $method = 'action' . $name . $by;
                if (method_exists($this, $method))
                    return $this->$method();
            }

            $method = 'action' . $name . 'Submitted';
            if (method_exists($this, $method))
                return $this->$method();
        }

        if ($this->action) {
            $method = 'actionNavi' . $this->action;
            if (method_exists($this, $method))
                $this->$method();
            else
                $this->actionUnknown($this->action);
        }

        $method = 'actionDefault';
        $this->$method();
    }


    /**
     * Default, unspecified action
     * @return void
     */
    protected function actionDefault()
    {}


    /**
     * Unknown action
     * @param string
     * @return void
     */
    protected function actionUnknown($action)
    {
        $this->throwError(NHttpResponse::S400_BAD_REQUEST, "Unknown action '$action'.");
    }



    public function isReachable()
    {
        return TRUE;
    }



    /**
     * Compares current and second page
     * @param  NPage
     * @return boolean  returns TRUE when both pages are the same
     */
    public function isEqual(NPage $page)
    {
        if (get_class($this) !== get_class($page)) return FALSE;

        if ($this->action !== $page->action) return FALSE;

        $list = self::getParamList(get_class($this));
        foreach ($list as $nm => $l)
            if ($this->$nm !== $page->$nm) return FALSE;

        return TRUE;
    }


    /**
     * @return NPage
     */
    public function link($class, $params=NULL, $action=NULL)
    {
        if (!$class) $class = get_class($this);
        if (!$params) $params = array();

        $list = self::getParamList($class);
        if ($list === FALSE) return FALSE;

        foreach ($list as $nm => $l)
            if (!array_key_exists($nm, $params) && $this instanceof $l['since'])
                $params[$nm] = $this->$nm;

        // debug
        $extra = array_diff_key($params, $list);
        if ($extra) {
            trigger_error("Extra parameters '" . implode(', ', array_keys($extra)) . "'.", E_USER_WARNING);
        }

        return new $class($params, $action);
    }



    public function getAction()
    {
        return $this->action;
    }


    /**
     * Returns parameters
     * @return array
     */
    public function getParams()
    {
        $params = array();
        $list = self::getParamList(get_class($this));
        foreach ($list as $nm => $l)
        {
            $params[$nm] = $this->$nm;
            if ($l['type']) settype($params[$nm], $l['type']);
            if ($l['def'] === $params[$nm]) $params[$nm] = NULL;
        }
        return $params;
    }


    /**
     * @param string
     * @return array
     */
    final static public function getParamList($class)
    {
        $class = strtolower($class);
        if (isset(self::$pCache[$class])) return self::$pCache[$class];

        // check class
        if (!class_exists($class, TRUE) || !is_subclass_of($class, 'NPage'))
            return self::$pCache[$class] = FALSE;

        // generate
        $list = array();
        $rc = new ReflectionClass($class);
        foreach ($rc->getDefaultProperties() as $nm => $val)
        {
            $rp = $rc->getProperty($nm);
            if (!$rp->isPublic()) continue;

            $decl = $rp->getDeclaringClass();
            // PHP bug fix
            while (($tmp = $decl->getParentClass()) && $tmp->hasProperty($nm) && $tmp->getProperty($nm)->isPublic())
                $decl = $tmp;

            $list[$nm] = array(
                'def' => $val, // default value
                'type' => $val === NULL ? NULL : gettype($val), // forced type
                'since' => $decl->getName(),
            );
        }

        return self::$pCache[$class] = $list;
    }


    /**
     * Maps NLink object to absolute URI or path
     * @return string
     */
    public function getUri()
    {
        return Nette::registry('router')->generateUri($this);
    }


    /**
     * Conditional redirect to canonicalized URI
     * @return void
     */
    protected function canonicalize()
    {
        if (NHttpRequest::getMethod() !== 'POST') {
            // build canonical URI
            $uri = $this->getUri();
            if ($uri && !NHttpRequest::isEqual($uri)) {
                $this->throwRedirect($uri, NHttpResponse::S301_MOVED_PERMANENTLY);
            }
        }
    }


    public function lastModified($lastModified, $expire=NULL)
    {
        if (NETTE_MODE === 'DEBUG') return;

        if ($expire !== NULL) NHttpResponse::expire($expire);

        $if = NHttpRequest::getHeaders('if-modified-since');
        if ($if !== NULL) {
            $if = strtotime($if);
            if ($lastModified <= $if) {
                NHttpResponse::setCode(304);
                exit;
            }
        }

        header('Last-Modified: ' . NHttpResponse::date($lastModified));

        // TODO: support for ETag
    }


    protected function registerForm(NForm $form)
    {
        $name = $form->getName();
        if ($name == NULL) $name = 'form';
        $this->$name = $form;
        if ($form->isSubmitted())
            $this->submittedForm = $form;
    }


    /**
     * Forward to another page
     * @param NPage
     * @return void
     */
    protected function throwForward($page)
    {
        throw new NDispatchException($page);
    }


    /**
     * Forward to error page
     * @param int  HTTP error code
     * @param string  error message
     * @param string  error page
     * @return void
     */
    protected function throwError($code, $message='')
    {
        if ($code < 400 || $code > 505) {
            throw new NetteException("Bad error HTTP code $code.");
        }

        $page = $this->link(Nette::$errorPage);
        $page->code = $code;
        $page->message = $message;
        throw new NDispatchException($page, TRUE);
    }


    /**
     * Redirect to another page
     * @param NPage
     * @param int HTTP error code
     * @return void
     */
    protected function throwRedirect($dest, $code=NHttpResponse::S303_SEE_OTHER)
    {
        if (!in_array($code, array(300, 301, 302, 303, 307))) {
            throw new NetteException("Bad redirect HTTP code '$code'.");
        }

        $page = $this->link(Nette::$redirectPage);
        $page->code = $code;
        $page->uri = $dest instanceof NPage ? $dest->getUri() : $dest;
        throw new NDispatchException($page, TRUE);
    }

}





/*
abstract class NAction extends NPage
{

    public function executeAction()
    {
        // HTTP 204 No Content
        NHttpResponse::setCode(204);
        parent::executeAction();
    }

    public function draw()
    {
    }

}
*/
