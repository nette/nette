<?php

/**
 * This file is part of the Nette Framework (http://nette.texy.info/)
 *
 * Copyright (c) 2005-2007 David Grudl aka -dgx- <dave@dgx.cz>
 *
 * @version  $Revision: 51 $ $Date: 2007-04-11 20:34:53 +0200 (st, 11 IV 2007) $
 * @package  Nette
 */





class NDefaultErrorPage extends NPage
{
    public $code = NHttpResponse::S500_INTERNAL_SERVER_ERROR;
    public $message;
    public $exception;


    public function isReachable()
    {
        return FALSE;
    }


    public function render()
    {
        if ($this->code)
            NHttpResponse::setCode($this->code);

        $msg = $this->message;
        if (!$msg) $message = 'Error ' . $this->code;

        echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">';
        echo '<meta name="robots" content="noindex,nofollow">';
        echo '<title>', htmlSpecialChars($msg), '</title>';
        echo '<h1>', htmlSpecialChars($msg), '</h1>';

        if ($this->exception) echo '<xmp>', $this->exception;
    }


}
