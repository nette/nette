<?php

/**
 * This file is part of the Nette Framework (http://nette.texy.info/)
 *
 * Copyright (c) 2005-2007 David Grudl aka -dgx- <dave@dgx.cz>
 *
 * @version  $Revision: 51 $ $Date: 2007-04-11 20:34:53 +0200 (st, 11 IV 2007) $
 * @package  Nette
 */


class NTemplate extends NObject
{
    static public $suffix = 'Page';

    private
        $fileName,
        $vars  = array(),
        $cache,
        $headers;


    public function __construct($fileName = NULL)
    {
        if ($fileName !== NULL) $this->setTemplate($fileName);
    }


    public function setTemplate($fileName)
    {
        if (!is_readable($fileName))
            throw new NetteException("Template '$fileName' is not readable.");

        $this->fileName = $fileName;
    }


    public function add($key, $value)
    {
        $this->vars[$key] = array('any', $value);
    }


    public function addHtml($key, $value)
    {
        $this->vars[$key] = array('any', $value);
    }


    public function addText($key, $value)
    {
        if (is_string($value)) $value = htmlSpecialChars($value, ENT_QUOTES);
        $this->vars[$key] = array('any', $value);
    }


    public function addTemplate($key, $fileName)
    {
        if ($fileName instanceof self) {
            $this->vars[$key] = array('any', $fileName);
            return;
        }

        if (!is_readable($fileName))
            throw new NetteException("Template '$fileName' is not readable.");

        $this->vars[$key] = array('file', $fileName);
    }


    public function addCallback($key, $callback, $method=NULL)
    {
        if ($method) $callback = array($callback, $method);

        if (!is_callable($callback))
            throw new NetteException("Callback is not callable.");

        $this->vars[$key] = array('callback', $callback);
    }



    public function addHeaders()
    {
        $this->headers = TRUE;
    }



    /**
     * Parses a URI into new NLink object
     * URI scheme must be 'nette' - i.e. nette:AnyPage/action?arg=value&next
     * @param string
     * @return NLink|FALSE
     */
    static public function link($uri)
    {
        $parts = parse_url($uri);
        if (!isset($parts['scheme']) || $parts['scheme'] != 'nette') return $uri;

        if (isset($parts['query'])) {
            parse_str($parts['query'], $params);
            NHttpRequest::fuckingQuotes(array(&$params));
            foreach ($params as $k => $v)
                if ($v === '') $params[$k] = NULL;
        } else {
            $params = array();
        }

        $class = ''; $action = NULL;
        if (isset($parts['path'])) {
            $tmp = explode('/', $parts['path']);
            $class = $tmp[0] . self::$suffix;
            if (isset($tmp[1]))
                $action = $tmp[1];
        }

        return Nette::link($class, $params, $action)->getUri();
    }




    public function render()
    {
        if ($this->fileName == NULL) return NULL;

        ob_start();
        $this->_render($this->fileName);
        $s = ob_get_clean();

        if ($this->headers) {
            header('Content-Length: ' . strlen($s), TRUE);
        }
        echo $s;
        return $s;
    }




    protected function _render($fileName)
    {
        $content = file_get_contents($fileName);

        // remove comments
        $content = preg_replace('#\{\{\*.*?\*\}\}#s', '', $content);

        // add root to relative links
        $content = preg_replace('#(src|href|action)\s*=\s*"(?![a-z]+:|/|<)#', '$1="' . rtrim(NETTE_WWW_URI, '\\/') . '/', $content);

        // translate nette links
        $content = preg_replace_callback('#(src|href|action)\s*=\s*"(nette:.*?)([\#"])#', array($this, 'linkCb'), $content);

        // replace variables
        $content = preg_replace('#\{\{(.*?)\}\}#s', '<?php \\$this->v(\'$1\')?>', $content);

        // translate texy blocks
        $content = preg_replace_callback('#<texy>(.*)</texy>#Us', array($this, 'texyCb'), $content);


        $this->cache = NETTE_TEMP_DIR . '/template.' . basename($fileName);
        file_put_contents($this->cache, $content);
        unset($content);

        foreach ($this->vars as $k => $arr) {
            if ($k === 'this') continue;

            if ($arr[0] === 'any') {
                if ($arr[1] instanceof self) {
                    ob_start();
                    $arr[1]->render();
                    $$k = ob_get_clean();
                } else {
                    $$k = $arr[1];
                }
            }
        }

        require $this->cache;
    }



    private function v($key)
    {
        list($type, $v) = $this->vars[$key];
        if ($type === 'any') {
            if ($v instanceof self) {
                $v->render();
            } else {
                echo $v;
            }
        } elseif ($type === 'file') {
            $this->_render($v);

        } elseif ($type === 'callback') {
            call_user_func($v);
        }
    }


    private function linkCb($m)
    {
        return $m[1] . '="' . htmlSpecialChars($this->link($m[2])) . $m[3];
    }


    private function texyCb($m)
    {
        $texy = new WikiTexy();
        return $texy->process($m[1]);
    }


}
