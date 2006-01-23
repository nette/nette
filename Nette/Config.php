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
    /** @var bool */
    protected $strict = FALSE;



    /********************* I/O operations ****************d*g**/


    /**
     * @param  array to wrap
     * @throws ::InvalidArgumentException
     */
    public function __construct($arr = NULL)
    {
        parent::__construct($arr);

        if ($arr !== NULL) {
            $this->setReadOnly();
        }
    }



    /**
     * Factory new configuration object from file.
     * @param  string  file name
     * @param  string  section to load
     * @param  bool    autoexpand variables
     * @return Config
     */
    public static function fromFile($file, $section = NULL, $expand = FALSE)
    {
        require_once dirname(__FILE__) . '/ConfigAdapters.php';

        $class = /*Nette::*/'ConfigAdapter_' . strtoupper(pathinfo($file, PATHINFO_EXTENSION));
        if (class_exists($class)) {
            return new self(call_user_func(array($class, 'load'), $file, $section, $expand));

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
        require_once dirname(__FILE__) . '/ConfigAdapters.php';

        $class = /*Nette::*/'ConfigAdapter_' . strtoupper(pathinfo($file, PATHINFO_EXTENSION));
        if (class_exists($class)) {
            return call_user_func(array($class, 'save'), $this, $file, $section);

        } else {
            throw new /*::*/InvalidArgumentException("Unknown file '$file' extension.");
        }
    }



    /********************* data access ****************d*g**/



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
            if ($val instanceof self) {
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
