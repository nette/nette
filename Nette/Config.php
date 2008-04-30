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
 * @package    Nette
 */

/*namespace Nette;*/


require_once dirname(__FILE__) . '/Collections/Hashtable.php';



/**
 * Configuration storage.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette
 * @version    $Revision$ $Date$
 */
class Config extends /*Nette::Collections::*/Hashtable
{
    /** flags: */
    const READONLY = 1;
    const EXPAND = 2;

    /** @var bool */
    protected $strict = FALSE;



    /********************* I/O operations ****************d*g**/


    /**
     * @param  array to wrap
     * @throws ::InvalidArgumentException
     */
    public function __construct($arr = NULL, $flags = self::READONLY)
    {
        parent::__construct($arr);

        if ($arr !== NULL) {
            if ($flags & self::EXPAND) {
                $this->expand();
            }

            if ($flags & self::READONLY) {
                $this->setReadOnly();
            }
        }
    }



    /**
     * Factory new configuration object from file.
     * @param  string  file name
     * @param  string  section to load
     * @param  int     flags (readOnly, autoexpand variables)
     * @return Config
     */
    public static function fromFile($file, $section = NULL, $flags = self::READONLY)
    {
        $class = /*Nette::*/'ConfigAdapter_' . strtoupper(pathinfo($file, PATHINFO_EXTENSION));
        if (class_exists($class)) {
            $arr = call_user_func(array($class, 'load'), $file, $section);
            return new /**/self/**//*static*/($arr, $flags);

        } else {
            throw new /*::*/InvalidArgumentException("Unknown file '$file' extension.");
        }
    }



    /**
     * Save configuration to file.
     * @param  string  file
     * @param  string  section to write
     * @return void
     */
    public function save($file, $section = NULL)
    {
        $class = /*Nette::*/'ConfigAdapter_' . strtoupper(pathinfo($file, PATHINFO_EXTENSION));
        if (class_exists($class)) {
            return call_user_func(array($class, 'save'), $this, $file, $section);

        } else {
            throw new /*::*/InvalidArgumentException("Unknown file '$file' extension.");
        }
    }



    /********************* data access ****************d*g**/



    /**
     * Expand all variables.
     * @return void
     */
    public function expand()
    {
        if ($this->readOnly) {
            throw new /*::*/NotSupportedException('Configuration is read-only.');
        }

        foreach ($this->data as $key => $val) {
            if (is_string($val)) {
                $this->data[$key] = Environment::expand($val);
            } elseif ($val instanceof self) {
                $val->expand();
            }
        }
    }



    /**
     * Prevent any more modifications.
     * @return void
     */
    public function setReadOnly()
    {
        $this->readOnly = TRUE;
        foreach ($this->data as $val) {
            if ($val instanceof /*Nette::Collections::*/Collection) {
                $val->setReadOnly();
            }
        }
    }



    /**
     * Import from array or any traversable object.
     * @param  array|Traversable
     * @return void
     * @throws ::InvalidArgumentException
     */
    public function import($arr)
    {
        parent::import($arr);

        foreach ($this->data as $key => $val) {
            if (is_array($val)) {
                $this->data[$key] = $obj = clone $this;
                $obj->data = array();
                $obj->import($val);
            }
        }
    }



    /**
     * Returns an array containing all of the elements in this collection.
     * @return array
     */
    public function toArray()
    {
        $res = $this->data;
        foreach ($res as $key => $val) {
            if ($val instanceof /*Nette::Collections::*/ICollection) {
                $res[$key] = $val->toArray();
            }
        }
        return $res;
    }



    /********************* overloading ****************d*g**/



    /**
     * Returns item. Do not call directly.
     * @param  int index
     * @return mixed
	 */
	protected function &__get($key)
	{
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        $null = NULL;
        return $null;
	}



	/**
	 * Inserts (replaces) item. Do not call directly.
     * @param  int index
     * @param  object
     * @return void
	 */
	protected function __set($key, $item)
	{
        $this->beforeAdd($item);
        $this->data[$key] = $item;
	}



    /**
     * Exists item?
     * @param  string  name
     * @return bool
     */
    protected function __isset($key)
    {
        return array_key_exists($key, $this->data);
    }



    /**
     * Removes the element at the specified position in this list.
     * @param  string  name
     * @return void
     */
    protected function __unset($key)
    {
        $this->beforeRemove();
        unset($this->data[$key]);
    }

}
