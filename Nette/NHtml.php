<?php

/**
 * This file is part of the Nette Framework (http://php7.org/nette/)
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004-2007 David Grudl aka -dgx- (http://www.dgx.cz)
 * @license    New BSD License
 * @version    $Revision: 89 $ $Date: 2007-10-25 08:17:37 +0200 (čt, 25 X 2007) $
 * @category   Nette
 * @package    Nette-Html
 */



/**
 * HTML helper
 *
 * usage:
 *       $anchor = NHtml::el('a')->href($link)->setText('Nette');
 *       $el->class = 'myclass';
 *       echo $el;
 *
 *       echo $el->startTag(), $el->endTag();
 *
 * Requirements:
 *     - PHP 5.0.4
 *     - NException
 *     - optionally NApplication
 *
 * @property mixed element's attributes
 */
class NHtml extends NObject implements ArrayAccess /*, Countable*/
{
    /** @var string  element's name */
    private $name;

    /** @var bool  is element empty? */
    private $isEmpty;

    /** @var array  element's attributes */
    public $attrs = array();

    /** @var array  of NHtml | string nodes */
    private $children = array();

    /** @var NHtml parent element */
    private $parent;

    /** @var bool  use XHTML syntax? */
    public static $xhtml = TRUE;

    /** @var array  empty elements */
    public static $emptyElements = array('img'=>1,'hr'=>1,'br'=>1,'input'=>1,'meta'=>1,'area'=>1,
        'base'=>1,'col'=>1,'link'=>1,'param'=>1,'basefont'=>1,'frame'=>1,'isindex'=>1,'wbr'=>1,'embed'=>1);



    /**
     * Static factory
     * @param string element name (or NULL)
     * @param array|string element's attributes (or textual content)
     * @return NHtml
     */
    public static function el($name = NULL, $attrs = NULL)
    {
        $el = new self;
        $el->setName($name);
        if (is_array($attrs)) {
            $el->attrs = $attrs;
        } elseif ($attrs !== NULL) {
            $el->setText($attrs);
        }
        return $el;
    }



    /**
     * Changes element's name
     * @param string
     * @param bool  Is element empty?
     * @throws NHtmlException
     * @return NHtml  provides a fluent interface
     */
    final public function setName($name, $empty = NULL)
    {
        if ($name !== NULL && !is_string($name)) {
            throw new NHtmlException("Name must be string or NULL");
        }

        $this->name = $name;
        $this->isEmpty = $empty === NULL ? isset(self::$emptyElements[$name]) : (bool) $empty;
        return $this;
    }



    /**
     * Returns element's name
     * @return string
     */
    final public function getName()
    {
        return $this->name;
    }



    /**
     * Is element empty?
     * @return bool
     */
    final public function isEmpty()
    {
        return $this->isEmpty;
    }



    /**
     * Overloaded setter for element's attribute
     * @param string    property name
     * @param mixed     property value
     * @return void
     */
    final public function __set($name, $value)
    {
        $this->attrs[$name] = $value;
    }



    /**
     * Overloaded getter for element's attribute
     * @param string    property name
     * @return mixed    property value
     */
    final public function &__get($name)
    {
        return $this->attrs[$name];
    }



    /**
     * Overloaded setter for element's attribute
     * @param string attribute name
     * @param array value
     * @return NHtml  provides a fluent interface
     */
    final public function __call($m, $args)
    {
        $this->attrs[$m] = $args[0];
        return $this;
    }



    /**
     * Special setter for element's attribute
     * @param string path
     * @param array query
     * @return NHtml  provides a fluent interface
     */
    final public function href($path, $query = NULL)
    {
        // for Nette framework
        if (class_exists('NApplication', FALSE)) {
            if (substr($path, 0, 6) === 'action:') {
                if ($query === NULL) $query = array();
                $this->attrs['href'] = NApplication::getController()->action(substr($path, 6))->setArgs($query)->constructUrl();
                return $this;

            } elseif (substr($path, 0, 5) === 'controller:') {
                if ($query === NULL) $query = array();
                $this->attrs['href'] = NApplication::getController()->controller(substr($path, 5))->setArgs($query)->constructUrl();
                return $this;
            }
        }

        if ($query) {
            $query = http_build_query($query, NULL, '&');
            if ($query !== '') $path .= '?' . $query;
        }
        $this->attrs['href'] = $path;
        return $this;
    }



    /**
     * Sets element's textual content
     * @param string
     * @param bool is the string HTML encoded yet?
     * @throws NHtmlException
     * @return NHtml  provides a fluent interface
     */
    final public function setText($text, $isHtml = FALSE)
    {
        if ($text === NULL) {
            $text = '';
        } elseif (!is_scalar($text)) {
            throw new NHtmlException("Textual content must be a scalar");
        }

        if (!$isHtml) {
            $text = str_replace(array('&', '<', '>'), array('&amp;', '&lt;', '&gt;'), $text);
        }

        $this->children = array($text);
        return $this;
    }



    /**
     * Gets element's textual content
     * @return string
     */
    final public function getText()
    {
        $s = '';
        foreach ($this->children as $child) {
            if (is_object($child)) return FALSE;
            $s .= $child;
        }
        return $s;
    }



    /**
     * Adds new element's child
     * @param NHtml|string child node
     * @return NHtml  provides a fluent interface
     */
    final public function add($child)
    {
        return $this->insert(NULL, $child);
    }



    /**
     * Creates and adds a new NHtml child
     * @param string  elements's name
     * @param array|string element's attributes (or textual content)
     * @return NHtml  created element
     */
    final public function create($name, $attrs = NULL)
    {
        $this->insert(NULL, $child = self::el($name, $attrs));
        return $child;
    }



    /**
     * Inserts child node
     * @param int
     * @param NHtml node
     * @param bool
     * @return NHtml  provides a fluent interface
     * @throws NHtmlException
     */
    public function insert($index, $child, $replace = FALSE)
    {
        if ($child instanceof NHtml) {
            if ($child->parent !== NULL) {
                throw new NHtmlException('Child node already has parent');
            }
            $child->parent = $this;

        } elseif (!is_string($child)) {
            throw new NHtmlException('Child node must be scalar or NHtml object');
        }

        if ($index === NULL)  { // append
            $this->children[] = $child;

        } else { // insert or replace
            array_splice($this->children, (int) $index, $replace ? 1 : 0, array($child));
        }

        return $this;
    }



    /**
     * Inserts (replaces) child node (ArrayAccess implementation)
     * @param int
     * @param NHtml node
     * @return void
     */
    public function offsetSet($index, $child)
    {
        $this->insert($index, $child, TRUE);
    }



    /**
     * Returns child node (ArrayAccess implementation)
     * @param int index
     * @return mixed
     */
    public function offsetGet($index)
    {
        return $this->children[$index];
    }



    /**
     * Exists child node? (ArrayAccess implementation)
     * @param int index
     * @return bool
     */
    public function offsetExists($index)
    {
        return isset($this->children[$index]);
    }



    /**
     * Removes child node (ArrayAccess implementation)
     * @param int index
     * @return void
     */
    public function offsetUnset($index)
    {
        if (isset($this->children[$index])) {
            $child = $this->children[$index];
            array_splice($this->children, (int) $index, 1);
            $child->parent = NULL;
        }
    }



    /** Countable implementation */
    public function count()
    {
        return count($this->children);
    }



    /**
     * Returns all of children
     * return array
     */
    function getChildren()
    {
        return $this->children;
    }



    /**
     * Returns parent node
     * @return NHtml
     */
    final public function getParent()
    {
        return $this->parent;
    }



    /**
     * Renders element's start tag, content and end tag
     * @return string
     */
    final public function render()
    {
        $s = $this->startTag();

        // empty elements are finished now
        if ($this->isEmpty) {
            return $s;
        }

        // add content
        foreach ($this->children as $child) {
            if (is_object($child)) {
                $s .= $child->render();
            } else {
                $s .= $child;
            }
        }

        // add end tag
        return $s . $this->endTag();
    }



    /**
     * Returns element's start tag
     * @return string
     */
    public function startTag()
    {
        if (!$this->name) {
            return '';
        }

        $s = '<' . $this->name;

        if (is_array($this->attrs)) {
            foreach ($this->attrs as $key => $value)
            {
                // skip NULLs and false boolean attributes
                if ($value === NULL || $value === FALSE) continue;

                // true boolean attribute
                if ($value === TRUE) {
                    // in XHTML must use unminimized form
                    if (self::$xhtml) $s .= ' ' . $key . '="' . $key . '"';
                    // in HTML should use minimized form
                    else $s .= ' ' . $key;
                    continue;

                } elseif (is_array($value)) {

                    // prepare into temporary array
                    $tmp = NULL;
                    foreach ($value as $k => $v) {
                        // skip NULLs & empty string; composite 'style' vs. 'others'
                        if ($v == NULL) continue;

                        if (is_string($k)) $tmp[] = $k . ':' . $v;
                        else $tmp[] = $v;
                    }

                    if (!$tmp) continue;
                    $value = implode($key === 'style' ? ';' : ' ', $tmp);
                }

                // add new attribute
                $s .= ' ' . $key . '="'
                    . str_replace(array('&', '"', '<', '>', '@'), array('&amp;', '&quot;', '&lt;', '&gt;', '&#64;'), $value)
                    . '"';
            }
        }

        // finish start tag
        if (self::$xhtml && $this->isEmpty) {
            return $s . ' />';
        }
        return $s . '>';
    }



    final public function __toString()
    {
        return $this->render();
    }



    /**
     * Returns element's end tag
     * @return string
     */
    public function endTag()
    {
        if ($this->name && !$this->isEmpty) {
            return '</' . $this->name . '>';
        }
        return '';
    }



    /**
     * Clones all children too
     */
    final public function __clone()
    {
        $this->parent = NULL;
        foreach ($this->children as $key => $value) {
            if (is_object($value)) {
                $this->children[$key] = clone $value;
            }
        }
    }

}


class NHtmlException extends NException
{
}
