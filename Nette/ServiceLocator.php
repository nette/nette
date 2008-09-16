<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette
 * @version    $Id$
 */

/*namespace Nette;*/



require_once dirname(__FILE__) . '/IServiceLocator.php';

require_once dirname(__FILE__) . '/Object.php';



/**
 * Service locator pattern implementation.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette
 */
class ServiceLocator extends Object implements IServiceLocator
{
	/** @var IServiceLocator */
	private $parent;

	/** @var array  storage for shared objects */
	private $registry = array();

	/** @var array  storage for shared objects */
	private $factories = array();



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
				throw new /*::*/InvalidArgumentException('Service seems to be callback, but service name is missing.');
			}

		} else {
			throw new /*::*/InvalidArgumentException('Service must be name, object or factory callback.');
		}

		$lower = strtolower($name);
		if (isset($this->registry[$lower])) {
			throw new AmbiguousServiceException("Service named '$name' has been already set.");
		}

		if (is_object($service)) {
			$this->registry[$lower] = $service;

		} else {
			$this->factories[$lower] = $service;
		}

		if ($promote && $this->parent !== NULL) {
			$this->parent->addService($service, $name, TRUE);
		}
	}



	/**
	 * Removes the specified service type from the service container.
	 * @param  bool   promote to higher level?
	 * @return void
	 */
	public function removeService($name, $promote = TRUE)
	{
		if (!is_string($name) || $name === '') {
			throw new /*::*/InvalidArgumentException('Service name must be a non-empty string.');
		}

		$lower = strtolower($name);
		unset($this->registry[$lower], $this->factories[$lower]);

		if ($promote && $this->parent !== NULL) {
			$this->parent->removeService($name, TRUE);
		}
	}



	/**
	 * Gets the service object of the specified type.
	 * @param  string service name
	 * @param  bool   throw exception if service doesn't exist?
	 * @return mixed
	 */
	public function getService($name, $need = TRUE)
	{
		if (!is_string($name) || $name === '') {
			throw new /*::*/InvalidArgumentException('Service name must be a non-empty string.');
		}

		$lower = strtolower($name);

		if (isset($this->registry[$lower])) {
			return $this->registry[$lower];

		} elseif (isset($this->factories[$lower])) {
			$service = $this->factories[$lower];

			if (is_string($service)) {
				if (substr($service, -2) === '()') {
					// trick to pass callback as string
					$service = substr($service, 0, -2);

				} else {
					/**/// fix for namespaced classes/interfaces in PHP < 5.3
					if ($a = strrpos($service, ':')) $service = substr($service, $a + 1);/**/

					if (!class_exists($service)) {
						throw new AmbiguousServiceException("Cannot instantiate service, class '$service' not found.");
					}
					return $this->registry[$lower] = new $service;
				}
			}

			return $this->registry[$lower] = call_user_func($service);
		}

		if ($this->parent !== NULL) {
			return $this->parent->getService($name);

		} elseif ($need) {
			throw new /*::*/InvalidStateException("Service '$name' not found.");

		} else {
			return NULL;
		}
	}



	/**
	 * Returns the parent container if any.
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
 */
class AmbiguousServiceException extends /*::*/Exception
{
}
