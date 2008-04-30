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


require_once dirname(__FILE__) . '/Component.php';

require_once dirname(__FILE__) . '/IComponentContainer.php';



/**
 * ComponentContainer is default implementation of IComponentContainer.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette
 * @version    $Revision$ $Date$
 */
class ComponentContainer extends Component implements IComponentContainer
{
    /** @var array of IComponent */
    private $components = array();

    /** @var bool */
    private $cloning = FALSE;



    /********************* interface IComponentContainer ****************d*g**/



    /**
     * Adds the specified component to the IComponentContainer.
     * @param  IComponent
     * @param  string
     * @return void
     * @throws ::InvalidStateException
     */
    public function addComponent(IComponent $component, $name)
    {
        if ($name === NULL) {
            $name = $component->getName();
        }

        if ($name == NULL) { // intentionally ==
            throw new /*::*/InvalidArgumentException('Component name is required.');
        }

        if (!is_string($name) || !preg_match('#^[a-z0-9_]+$#', $name)) {
            throw new /*::*/InvalidArgumentException("Component name must be non-empty alphanumeric string, '$name' is invalid.");
        }

        if (isset($this->components[$name])) {
            throw new /*::*/InvalidStateException("Component with name '$name' already exists.");
        }

        // check circular reference
        $obj = $this;
        do {
            if ($obj === $component) {
                throw new /*::*/InvalidStateException("Component is (grand) parent of container.");
            }
            $obj = $obj->getParent();
        } while ($obj !== NULL);

        // userland checking
        $this->validateChildComponent($component);

        try {
            $this->components[$name] = $component;
            $component->setParent($this, $name);

        } catch (Exception $e) {
            unset($this->components[$name]); // undo
            throw $e;
        }
    }



    /**
     * Removes a component from the IComponentContainer.
     * @param  IComponent
     * @return void
     */
    public function removeComponent(IComponent $component)
    {
        $name = $component->getName();
        if (!isset($this->components[$name]) || $this->components[$name] !== $component) {
            throw new /*::*/InvalidArgumentException("Component is not located in this container.");
        }

        unset($this->components[$name]);
        $component->setParent(NULL);
    }



    /**
     * Returns single component or NULL.
     * @param  string
     * @param  bool   throws exception if component didn't exist?
     * @return IComponent|NULL
     */
    final public function getComponent($name, $need = FALSE)
    {
        if (isset($this->components[$name])) {
            return $this->components[$name];
        } elseif ($need) {
            throw new /*::*/InvalidArgumentException("Component with name '$name' does not exist.");
        } else {
            return NULL;
        }
    }



    /**
     * Returns collection of all the components in the container.
     * @return array of IComponent
     */
    final public function getComponents()
    {
        return $this->components;
    }



    /**
     * Descendant can override this method to disallow insert a child by throwing an ::InvalidStateException.
     * @param  IComponent
     * @return void
     * @throws ::InvalidStateException
     */
    protected function validateChildComponent(IComponent $child)
    {
    }



    /********************* hierarchy notifications ****************d*g**/



    /**
     * This helper invokes specified method of all components in IComponent & IComponentContainer hierarchy recursively.
     * @param  IComponentContainer
     * @param  string  method name
     * @param  array   arguments
     * @param  string  class/interface name
     * @return void
     */
    static public function notifyComponents(IComponentContainer $container, $method, array $args = array(), $type = 'Nette::Component')
    {
        /**/// fix for namespaced classes/interfaces in PHP < 5.3
        if ($a = strrpos($type, ':')) $type = substr($type, $a + 1);/**/

        foreach ($container->getComponents() as $component) {
            if ($component instanceof $type) {
                call_user_func_array(array($component, $method), $args);
            }

            if ($component instanceof IComponentContainer) {
                self::notifyComponents($component, $method, $args, $type);
            }
        }
    }



    /********************* cloneable, serializable ****************d*g**/



    /**
     * Object cloning.
     */
    public function __clone()
    {
        parent::__clone();

        if ($this->components) {
            $oldMyself = reset($this->components)->getParent();
            $oldMyself->cloning = TRUE;
            foreach ($this->components as $name => $component) {
                $this->components[$name] = clone $component;
            }
            $oldMyself->cloning = FALSE;
        }
    }



    /**
     * Is container cloning now? (for internal usage).
     */
    public function isCloning()
    {
        return $this->cloning;
    }

}
