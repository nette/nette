<?php

/**
 * This file is part of the Nette Framework (http://nette.texy.info/)
 *
 * Copyright (c) 2005-2007 David Grudl aka -dgx- <dave@dgx.cz>
 *
 * @version  $Revision: 51 $ $Date: 2007-04-11 20:34:53 +0200 (st, 11 IV 2007) $
 * @package  Nette
 */





class NDefaultRedirectPage extends NPage
{
    public $code;
    public $uri;


    public function isReachable()
    {
        return FALSE;
    }


    public function render()
    {
        $uri = $this->uri;
        if (substr($uri, 0, 2) === '//')
            $uri = NHttpRequest::getScheme() . $uri;
        elseif (substr($uri, 0, 1) === '/')
            $uri = NHttpRequest::getScheme() . '//' . NHttpRequest::getHost() . $uri;

        NHttpResponse::setCode($this->code);
        NHttpResponse::setHeader('Location: ' . $uri);

        echo '<title>Redirect</title>';
        echo '<h1>Redirect</h1>';
        echo '<p><a href="', htmlSpecialChars($this->uri), '">Please click here to continue</a>.</p>';
    }

}
