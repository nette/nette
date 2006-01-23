<?php

/**
 * This file is part of the Nette Framework (http://nette.texy.info/)
 *
 * Copyright (c) 2005-2007 David Grudl aka -dgx- <dave@dgx.cz>
 *
 * @version  $Revision: 51 $ $Date: 2007-04-11 20:34:53 +0200 (st, 11 IV 2007) $
 * @package  Nette
 */



class NDOM extends DOMDocument
{

    /**
     * @param string
     * @return bool
     */
    public function load($fileName)
    {
        return $this->loadXML(file_get_contents($fileName));
    }


    /**
     * @param string
     * @return bool
     */
    public function loadXML($s)
    {
        if (!isset($this) || !($this instanceof self))
            die('Nette: Don\'t call loadXML statically');

        // prepare entities: &ndash; => &#8211; etc...
        // protect entites < > "
        $s = strtr($s, "\x05\x06\x07<>\"", "   \x05\x06\x07");
        // decode quotes
        $s = html_entity_decode($s, ENT_QUOTES, 'UTF-8'); // ENT_NOQUOTES converts &#x22;
        // htmlSpecialChars
        $s = str_replace(array('&', '<', '>', '"'), array('&amp;', '&lt;', '&gt;', '&quot;'), $s);
        // pass back protected entities
        $s = strtr($s, "\x05\x06\x07", '<>"');

        // prepare getElementById: rename id="..." to xml:id="..."
        $s = preg_replace('#((?><[a-zA-Z][^>"\s]*\s*)(?>[^>"]+"[^"]*"\s*)*?)id="#', '$1xml:id="', $s);

        // prepare {{var}} to <span title="var"></span>
//        $s = preg_replace('#\{\{([^\s}]+?)\}\}#', '<span xml:id="_$1">$0</span>', $s);

        // configure
        $this->resolveExternals = FALSE;
        $this->validateOnParse = FALSE;
        $this->strictErrorChecking = TRUE;
        $this->preserveWhiteSpace = TRUE;

        // parse XML
        return parent::loadXML($s);
    }



    /**
     * @param DOMNode
     * @return string
     */
    public function saveXML($node = NULL)
    {
        // configure
        $this->encoding = 'utf-8';
        $this->formatOutput = FALSE;

        // generate XML
        $s = parent::saveXML($node);

        // rename xml:id="..." to id="..."  &   remove xml:id="_..."
        $s = preg_replace('#((?><[a-zA-Z][^>"\s]*\s*)(?>[^>"]+"[^"]*"\s*)*?)xml:(?:id="_[^"]+"\s*|(id="))#', '$1$2', $s);
        $s = str_replace(' >', '>', $s); // !!!

        // replace cache
        $s = preg_replace_callback('#<\?cache (\d+)\?>#', array($this, 'cachecb'), $s);
        return $s;
    }



    /**
     * @param string
     * @return SimpleXMLElement
     */
    public function simple($id = NULL)
    {
        if ($id) {
            $node = $this->getElementById($id);
            if (!$node) return FALSE;
            return simplexml_import_dom($node);
        }
        return simplexml_import_dom($this);
    }


    /**
     * @param DOMNode|SimpleXMLElement
     * @return void
     */
    static public function clean($node)
    {
        if ($node instanceof SimpleXMLElement)
            $node = dom_import_simplexml($node);

        $node->nodeValue = '';
    }


    /**
     * @param DOMNode|SimpleXMLElement
     * @return void
     */
    static public function remove($node)
    {
        if ($node instanceof SimpleXMLElement)
            $node = dom_import_simplexml($node);

        $node->parentNode->removeChild($node);
    }


    /**
     * @param DOMNode
     * @param string
     * @return bool
     */
    public function appendXML($node, $s)
    {
    /*
        $frag = $node->ownerDocument->createDocumentFragment();
        $frag->appendXML($s);
        $node->appendChild($frag);
    */
        $new = new DOMDocument();
        $ok = $new->loadXML('<xml>'.$s.'</xml>');
        if (!$ok) return FALSE;

        $new = $node->ownerDocument->importNode($new->documentElement, TRUE);
        $child = $new->firstChild;
        while ($child) {
            $nextChild = $child->nextSibling;
            $node->appendChild($child);
            $child = $nextChild;
        }
        unset($new);

        return true;
    }




    /**
     * @param DOMNode|SimpleXMLElement
     * @return void
     */
    public function dump($node = NULL)
    {
        if ($node instanceof SimpleXMLElement)
            $node = dom_import_simplexml($node);

        $xml = $this->saveXML($node);
        $f = new NetteFormatter();
        $xml = $f->format($xml);
        echo '<pre style="border:1px solid silver; margin:1em">', htmlSpecialChars($xml), '</pre>';
    }


    private $cache = array();

    public function createCache($value)
    {
        $key = count($this->cache);
        $this->cache[] = $value;

        return new DOMCache('cache', $key);
    }

    private function cachecb($m)
    {
        return $this->cache[$m[1]];
    }

}



class DOMCache extends DOMProcessingInstruction
{
}








class NetteFormatter
{
    public  $lineWrap = 160;
    private $indent;

    private $hashTable = array();





    function format($text)
    {
        $this->indent = 0;

        // freeze all pre, textarea, script and style elements
        $text = preg_replace_callback(
                       '#<(pre|textarea|script|style)(.*)</\\1>#Uis',
                       array(&$this, '_freeze'),
                       $text
        );

        // remove \n
        $text = str_replace("\n", '', $text);

        // shrink multiple spaces
        $text = preg_replace('# +#', ' ', $text);

        // indent all block elements + br
        $block = array('head', 'meta', 'link', 'body', 'html',    'address','blockquote','caption','col','colgroup','dd','div','dl','dt','fieldset','form','h1','h2','h3','h4','h5','h6','hr','iframe','legend','li','object','ol','p','param','pre','table','tbody','td','tfoot','th','thead','tr','ul','embed',);

        $text = preg_replace_callback(
                       '# *<(/?)(' . implode('|', $block) . '|br)(>| [^>]*>) *#i',
                       array(&$this, '_replaceReformat'),
                       $text
        );

        // right trim
        $text = preg_replace("#[\t ]+(\n|\r|$)#", '$1', $text); // right trim

        // join double \r to single \n
        $text = strtr($text, array("\r\r" => "\n", "\r" => "\n"));

        // "backtabulators"
        $text = strtr($text, array("\t\x08" => '', "\x08" => ''));

        // line wrap
        if ($this->lineWrap > 0)
            $text = preg_replace_callback(
                             '#^(\t*)(.*)$#m',
                             array(&$this, '_replaceWrapLines'),
                             $text
            );

        // tabs -> spaces
        $text = strtr($text, array("\t" => '    '));

        // unfreeze pre, textarea, script and style elements
        $text = strtr($text, $this->hashTable);

        return $text;
    }





    // create new unique key for string $matches[0]
    // and saves pair (key => str) into table $this->hashTable
    function _freeze($matches)
    {
        static $counter = 0;
        $key = '<'.$matches[1].'>'
             . "\x1A" . strtr(base_convert(++$counter, 10, 4), '0123', "\x1B\x1C\x1D\x1E") . "\x1A"
             . '</'.$matches[1].'>';
        $this->hashTable[$key] = $matches[0];
        return $key;
    }




    /**
     * Callback function: Insert \n + spaces into HTML code
     * @return string
     */
    function _replaceReformat($matches)
    {
        list($match, $mClosing, $mTag) = $matches;
        //    [1] => /  (opening or closing element)
        //    [2] => element
        //    [3] => attributes>
        $match = trim($match);
        $mTag = strtolower($mTag);

        if ($mTag === 'br')  // exception
            return "\n"
                   . str_repeat("\t", max(0, $this->indent - 1))
                   . $match;

       static $empty = array('img'=>1,'hr'=>1,'br'=>1,'input'=>1,'meta'=>1,'area'=>1,'base'=>1,'col'=>1,'link'=>1,'param'=>1);
       if (isset($empty[$mTag]))
            return "\r"
                   . str_repeat("\t", $this->indent)
                   . $match
                   . "\r"
                   . str_repeat("\t", $this->indent);

        if ($mClosing === '/') {
            return "\x08"   // backspace
                   . $match
                   . "\n"
                   . str_repeat("\t", --$this->indent);
        }

        return "\n"
               . str_repeat("\t", $this->indent++)
               . $match;
    }




    /**
     * Callback function: wrap lines
     * @return string
     */
    function _replaceWrapLines($matches)
    {
        list(, $mSpace, $mContent) = $matches;
        return $mSpace
               . str_replace(
                      "\n",
                      "\n".$mSpace,
                      wordwrap($mContent, $this->lineWrap)
                 );
    }



}
