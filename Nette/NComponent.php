<?php

/**
 * This file is part of the Nette Framework (http://php7.org/nette/)
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004-2007 David Grudl aka -dgx- (http://www.dgx.cz)
 * @license    New BSD License
 * @version    $Revision: 69 $ $Date: 2007-06-27 19:13:46 +0200 (st, 27 VI 2007) $
 * @category   Nette
 * @package    Nette-Core
 */



class NComponent extends NObject implements ArrayAccess, IteratorAggregate
{
    /** @var array */
    private $components = array();

    /** @var string */
    protected $name;

    /** @var string */
    private $componentId;

    /** @var NComponent */
    private $owner;


    /**
     * Inserts a child component to this container
     * @param NComponent
     * @param string
     * @return void
     */
    protected function addComponent(NComponent $child, $name)
    {
        if (!is_string($name) || $name === '') {
            throw new NetteException('Invalid component name');
        }

        if (isset($this->components[$name])) {
            throw new NetteException("Component '$name' already exists");
        }

        if ($child->owner) {
            throw new NetteException("Component already has owner");
        }

        $child->name = $name;
        $child->owner = $this;
        $this->components[$name] = $child;
    }


    /**
     * Removes a child component from this container
     * @param string
     * @return void
     */
    protected function removeComponent($name)
    {
        if (!isset($this->components[$name])) {
            throw new NetteException("There is not component named '$name'");
        }

        unset($this->components[$name]);
    }


    /**
     * Adds new component into the container.
     * Required by the ArrayAccess interface.
     *
     * @param mixed  index to set or NULL for append
     * @param mixed  value
     * @return void
     * @throws NetteException
     */
    final public function offsetSet($name, $child)
    {
        if ($child === NULL) {
            $this->removeComponent($name);
        } else {
            $this->addComponent($child, $name);
        }
    }


    /**
     * Returns component specified by name.
     * Required by the ArrayAccess interface.
     *
     * @param mixed  index to get
     * @return mixed
     * @throws NetteException
     */
    final public function offsetGet($name)
    {
        if (!isset($this->components[$name])) {
            throw new NetteException("Undefined component '$name'");
        }

        return $this->components[$name];
    }

    /**
     * Does component specified by name exists?
     * Required by the ArrayAccess interface.
     *
     * @param mixed  index to check for existence
     * @return boolean
     */
    final public function offsetExists($name)
    {
        return isset($this->components[$name]);
    }

    /**
     * Removes component from the container.
     * Required by the ArrayAccess interface.
     *
     * @param mixed  index to unset
     * @return void
     */
    final public function offsetUnset($name)
    {
        $this->removeComponent($name);
    }


    /**
     * Required by the IteratorAggregate interface
     *
     * @return ArrayIterator
     */
    final public function getIterator()
    {
        return new ArrayIterator($this->components);
    }


    /**
     * @return array
     */
    final public function getComponents()
    {
        return $this->components;
    }


    /**
     * Returns the owner of this component
     * @return NComponent
     */
    final public function getOwner()
    {
        return $this->owner;
    }


    /**
     * @return string
     */
    final public function getName()
    {
        return $this->name;
    }


    public function __clone()
    {
        throw new NetteException('Clone is not allowed');
    }

}
