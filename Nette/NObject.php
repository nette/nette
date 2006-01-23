<?php

/**
 * This file is part of the Nette Framework (http://php7.org/nette/)
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004-2007 David Grudl aka -dgx- (http://www.dgx.cz)
 * @license    New BSD License
 * @version    $Revision: 85 $ $Date: 2007-07-09 03:29:31 +0200 (po, 09 VII 2007) $
 * @category   Nette
 * @package    Nette-Core
 */



/**
 * The super class of all the Nette classes, enhances PHP
 */
abstract class NObject
{
    /** @var ReflectionClass */
    private $reflection;

    /** @var array */
    private static $extends;



    /**
     * Returns the name of the class of this object
     * @return string
     */
    final public function getClass()
    {
        return get_class($this);
    }



    /**
     * Returns the name of the class of this object
     * @return ReflectionClass
     */
    final public function getReflection()
    {
        if ($this->reflection === NULL) {
            return $this->reflection = new ReflectionClass(get_class($this));
        }

        return $this->reflection;
    }


    /**
     * Adds method to class
     */
    final static function extend($class, $method)
    {
        // will be implemented in PHP 5.3.x
    }



    /**
     * Call to undefined method
     * @throws Exception
     */
    private function __call($name, $args)
    {
        throw new Exception("Call to undefined method " . get_class($this) . "::$name()");
    }



    /**#@+
     * Access to undeclared property
     * @throws Exception
     */
    private function &__get($name)
    {
        throw new Exception("Access to undeclared property " . get_class($this) . "::$$name");
    }



    private function __set($name, $value)
    {
        throw new Exception("Access to undeclared property " . get_class($this) . "::$$name");
    }



    private function __unset($name)
    {
        throw new Exception("Access to undeclared property " . get_class($this) . "::$$name");
    }
    /**#@-*/

}
