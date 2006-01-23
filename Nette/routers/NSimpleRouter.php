<?php

/**
 * This file is part of the Nette Framework (http://nette.texy.info/)
 *
 * Copyright (c) 2005-2007 David Grudl aka -dgx- <dave@dgx.cz>
 *
 * @version  $Revision: 111 $ $Date: 2007-03-30 14:09:50 +0200 (p+í, 30 III 2007) $
 * @package  Nette
 */


/**
 * Basic bidirectional router with no URL mapping
 */
class NSimpleRouter extends NRouter
{
    /** @var string */
    public $defaultClass;


    public function __construct($defaultClass)
    {
        $this->defaultClass = $defaultClass;
        parent::__construct();
    }


    public function route()
    {
        $class = isset($this->params['pg']) ? (string) $this->params['pg'] : $this->defaultClass;
        $action = isset($this->params['act']) ? $this->params['act'] : '';
        unset($this->params['pg'], $this->params['act']);
        return $this->factoryPage($class, $this->params, $action);
    }


    public function generateUri(NPage $page)
    {
        $params = $page->getParams();
        $params['pg'] = get_class($page);
        $params['act'] = $page->getAction();

        // remove NULLs
        foreach ($params as $k => $v) if ($v===NULL) unset($params[$k]);

        // build query
        return $this->getRoot() . '?' . http_build_query($params);
    }
}