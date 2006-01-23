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


require_once dirname(__FILE__) . '/IServiceLocator.php';

require_once dirname(__FILE__) . '/Object.php';



/**
 * Service locator pattern implementation (experimental).
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette
 * @version    $Revision$ $Date$
 */
class ServiceLocator extends Object implements IServiceLocator
{
    /** @var IServiceLocator */
    private $parent;

    /** @var array  storage for shared objects */
    private $registry = array();

    /** @var bool */
    private $autoDiscovery = TRUE;



    /**
     * @param  IServiceLocator
     */
    public function __construct(IServiceLocator $parent = NULL)
    {
        $this->parent = $parent;
    }



    /**
     * Adds the specified service to the service container.
     * @param  mixed  object, class name or service factory callback
     * @param  string optional service name (for factories is not optional)
     * @param  bool   promote to higher level?
     * @return void
     * @throws ::InvalidArgumentException, Exception
     */
    public function addService($service, $type = NULL, $promote = FALSE)
    {
        if (is_object($service)) {
            if ($type === NULL) $type = get_class($service);

        } elseif (is_string($service)) {
            if ($type === NULL) $type = $service;

        } elseif (is_callable($service, TRUE)) {
            if (empty($type)) {
                throw new /*::*/InvalidArgumentException('Missing service name.');
            }

        } else {
            throw new /*::*/InvalidArgumentException('Service must be class/interface name, object or factory callback.');
        }


        /**/// fix for namespaced classes/interfaces in PHP < 5.3
        if ($a = strrpos($type, ':')) $type = substr($type, $a + 1);/**/

        if (class_exists($type)) {
            foreach (class_implements($type) as $class) {
                $this->registry[strtolower($class)][] = $service;
            }

            foreach (class_parents($type) as $class) {
                $this->registry[strtolower($class)][] = $service;
            }
        }

        $lType = strtolower($type);
        if (!empty($this->registry[$lType])) {
            throw new AmbiguousServiceException("Ambiguous service '$type'.");
        }
        $this->registry[$lType][] = $service;


        if ($promote && $this->parent !== NULL) {
            $this->parent->addService($service, $type, TRUE);
        }
    }



    /**
     * Removes the specified service type from the service container.
     */
    public function removeService($type, $promote = FALSE)
    {
        if (!is_string($type)) {
            throw new /*::*/InvalidArgumentException('Service must be class/interface name.');
        }

        // not implemented yet

        if ($promote && $this->parent !== NULL) {
            $this->parent->removeService($type, TRUE);
        }
    }



    /**
     * Gets the service object of the specified type.
     */
    public function getService($type)
    {
        if (!is_string($type) || $type === '') {
            throw new /*::*/InvalidArgumentException('Service must be class/interface name.');
        }

        /**/// fix for namespaced classes/interfaces in PHP < 5.3
        if ($a = strrpos($type, ':')) $type = substr($type, $a + 1);/**/

        $lType = strtolower($type);

        if (isset($this->registry[$lType])) {
            if (count($this->registry[$lType]) > 1) {
                throw new AmbiguousServiceException("Ambiguous service '$type' resolution.");
            }

            $obj = $this->registry[$lType][0];
            if (is_object($obj)) {
                return $obj;

            } elseif (is_string($obj)) {
                /**/// fix for namespaced classes/interfaces in PHP < 5.3
                if ($a = strrpos($obj, ':')) $obj = substr($obj, $a + 1);/**/

                return $this->registry[$lType][0] = new $obj;

            } else {
                return $this->registry[$lType][0] = call_user_func($obj);
            }

        } elseif ($this->autoDiscovery) {
            if (class_exists($type)) {
                return $this->registry[$lType][0] = new $type;
            }

        } elseif ($this->parent !== NULL) {
            return $this->parent->getService($type);
        }

        return NULL;
    }



    /**
     * Returns the container if any.
     * @return IServiceLocator|NULL
     */
    public function getParent()
    {
        return $this->parent;
    }

}



/**
 * Ambiguous service resolution exception
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette
 * @version    $Revision$ $Date$
 */
class AmbiguousServiceException extends /*::*/Exception
{
}
