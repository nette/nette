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
	 * @throws ::InvalidArgumentException, AmbiguousServiceException
	 */
	public function addService($service, $name = NULL, $promote = FALSE)
	{
		if (is_object($service)) {
			if ($name === NULL) $name = get_class($service);

		} elseif (is_string($service)) {
			if ($name === NULL) $name = $service;

		} elseif (is_callable($service, TRUE)) {
			if (empty($name)) {
				throw new /*::*/InvalidArgumentException('Missing service name.');
			}

		} else {
			throw new /*::*/InvalidArgumentException('Service must be class/interface name, object or factory callback.');
		}

		$lower = strtolower($name);
		if (isset($this->registry[$lower]) && is_object($this->registry[$lower])) {
			throw new AmbiguousServiceException("Ambiguous service '$name'.");
		}
		$this->registry[$lower] = $service;

		if ($promote && $this->parent !== NULL) {
			$this->parent->addService($service, $name, TRUE);
		}
	}



	/**
	 * Removes the specified service type from the service container.
	 * @param  bool   promote to higher level?
	 * @return void
	 */
	public function removeService($name, $promote = FALSE)
	{
		if (!is_string($name) || $name === '') {
			throw new /*::*/InvalidArgumentException('Service name must be a non-empty string.');
		}

		// not implemented yet

		if ($promote && $this->parent !== NULL) {
			$this->parent->removeService($name, TRUE);
		}
	}



	/**
	 * Gets the service object of the specified type.
	 * @param  string service name
	 * @return void
	 */
	public function getService($name)
	{
		if (!is_string($name) || $name === '') {
			throw new /*::*/InvalidArgumentException('Service name must be a non-empty string.');
		}

		$lower = strtolower($name);

		if (isset($this->registry[$lower])) {
			$service = $this->registry[$lower];
			if (is_object($service)) {
				return $service;
			}

			if (is_string($service)) {
				if (substr($service, -2) === '()') {
					// trick to pass callback as string
					$service = substr($service, 0, -2);

				} else {
					/**/// fix for namespaced classes/interfaces in PHP < 5.3
					if ($a = strrpos($service, ':')) $service = substr($service, $a + 1);/**/

					if (!class_exists($service)) {
						throw new AmbiguousServiceException("Class '$service' not found.");
					}
					return $this->registry[$lower] = new $service;
				}
			}

			return $this->registry[$lower] = call_user_func($service);

		} elseif ($this->autoDiscovery) {
			/**/// fix for namespaced classes/interfaces in PHP < 5.3
			if ($a = strrpos($name, ':')) $name = substr($name, $a + 1);/**/

			if (class_exists($name)) {
				return $this->registry[$lower] = new $name;
			}

		} elseif ($this->parent !== NULL) {
			return $this->parent->getService($name);
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
 * Ambiguous service resolution exception.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette
 * @version    $Revision$ $Date$
 */
class AmbiguousServiceException extends /*::*/Exception
{
}
