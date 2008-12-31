<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette
 * @version    $Id$
 */

/*namespace Nette;*/



require_once dirname(__FILE__) . '/IComponent.php';

require_once dirname(__FILE__) . '/Object.php';



/**
 * Component is the base class for all components.
 *
 * Components are objects implementing IComponent. They has parent component,
 * own name and service locator.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette
 */
abstract class Component extends Object implements IComponent
{
	/** @var IServiceLocator */
	private $serviceLocator;

	/** @var IComponentContainer */
	private $parent;

	/** @var string */
	private $name;

	/** @var array of [type => [obj, depth, path, isMonitored?]] or NULL (monitored but not processed yet) */
	private $monitors = array();



	/**
	 */
	public function __construct(IComponentContainer $parent = NULL, $name = NULL)
	{
		if ($parent !== NULL) {
			$parent->addComponent($this, $name);

		} elseif (is_string($name)) {
			$this->name = $name;
		}
	}



	/**
	 * Lookup hierarchy for component specified by class or interface name.
	 * @param  string class/interface type
	 * @param  bool   throw exception if component doesn't exist?
	 * @return IComponent
	 */
	public function lookup($type, $need = TRUE)
	{
		/**/fixNamespace($type);/**/

		if (!isset($this->monitors[$type])) { // not monitored or not processed yet
			$obj = $this->parent;
			$path = self::NAME_SEPARATOR . $this->name;
			$depth = 1;
			while ($obj !== NULL) {
				if ($obj instanceof $type) break;
				$path = self::NAME_SEPARATOR . $obj->getName() . $path;
				$depth++;
				$obj = $obj->getParent(); // IComponent::getParent()
				if ($obj === $this) $obj = NULL; // prevent cycling
			}

			$monitored = array_key_exists($type, $this->monitors);
			if ($obj) {
				$this->monitors[$type] = array(
					$obj,
					$depth,
					substr($path, 1),
					$monitored,
				);
				if ($monitored) {
					$this->attached($obj);
				}
			} else {
				$this->monitors[$type] = array(NULL, NULL, NULL, $monitored); // not found
			}
		}

		if ($need && $this->monitors[$type][0] === NULL) {
			throw new /*\*/InvalidStateException("Component is not attached to '$type'.");
		}

		return $this->monitors[$type][0];
	}



	/**
	 * Lookup for component specified by class or interface name. Returns backtrace path.
	 * A path is the concatenation of component names separated by self::NAME_SEPARATOR.
	 * @param  string class/interface type
	 * @param  bool   throw exception if component doesn't exist?
	 * @return string
	 */
	public function lookupPath($type, $need = TRUE)
	{
		/**/fixNamespace($type);/**/
		$this->lookup($type, $need);
		return $this->monitors[$type][2];
	}



	/**
	 * Starts monitoring.
	 * @param  string class/interface type
	 * @return void
	 */
	public function monitor($type)
	{
		/**/fixNamespace($type);/**/
		$this->monitors[$type] = NULL;
		$this->lookup($type, FALSE); // call attached()
	}



	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 * @param  IComponent
	 * @return void
	 */
	protected function attached($obj)
	{
	}



	/**
	 * This method will be called before the component (or component's parent)
	 * becomes detached from a monitored object. Do not call this method yourself.
	 * @param  IComponent
	 * @return void
	 */
	protected function detached($obj)
	{
	}



	/********************* interface IComponent ****************d*g**/



	/**
	 * @return string
	 */
	final public function getName()
	{
		return $this->name;
	}



	/**
	 * Returns the container if any.
	 * @return IComponentContainer|NULL
	 */
	final public function getParent()
	{
		return $this->parent;
	}



	/**
	 * Sets the parent of this component. This method is managed by containers and should.
	 * not be called by applications
	 *
	 * @param  IComponentContainer  New parent or null if this component is being removed from a parent
	 * @param  string
	 * @return void
	 * @throws \InvalidStateException
	 */
	public function setParent(IComponentContainer $parent = NULL, $name = NULL)
	{
		if ($parent === NULL && $this->parent === NULL && $name !== NULL) {
			$this->name = $name; // just rename
			return;

		} elseif ($parent === $this->parent && $name === NULL) {
			return; // nothing to do
		}

		// A component cannot be given a parent if it already has a parent.
		if ($this->parent !== NULL && $parent !== NULL) {
			throw new /*\*/InvalidStateException('Component already has a parent.');
		}

		// remove from parent?
		if ($parent === NULL) {
			// parent cannot be removed if is still this component contains
			if ($this->parent->getComponent($this->name, FALSE) === $this) {
				throw new /*\*/InvalidStateException('The current parent still recognizes this component as its child.');
			}

			$this->refreshMonitors(0);
			$this->parent = NULL;

		} else { // add to parent
			// Given parent container does not already recognize this component as its child.
			if ($parent->getComponent($name, FALSE) !== $this) {
				throw new /*\*/InvalidStateException('The given parent does not recognize this component as its child.');
			}

			$this->validateParent($parent);
			$this->parent = $parent;
			if ($name !== NULL) $this->name = $name;

			$tmp = array();
			$this->refreshMonitors(0, $tmp);
		}
	}



	/**
	 * Is called by a component when it is about to be set new parent. Descendant can.
	 * override this method to disallow a parent change by throwing an \InvalidStateException
	 * @param  IComponentContainer
	 * @return void
	 * @throws \InvalidStateException
	 */
	protected function validateParent(IComponentContainer $parent)
	{
	}



	/**
	 * Refreshes monitors.
	 * @param  int
	 * @param  array|NULL (array = attaching, NULL = detaching)
	 * @return void
	 */
	private function refreshMonitors($depth, & $missing = NULL)
	{
		if ($this instanceof IComponentContainer) {
			foreach ($this->getComponents() as $component) {
				if ($component instanceof Component) {
					$component->refreshMonitors($depth + 1, $missing);
				}
			}
		}

		if ($missing === NULL) { // detaching
			foreach ($this->monitors as $type => $rec) {
				if (isset($rec[1]) && $rec[1] > $depth) {
					if ($rec[3]) { // monitored
						$this->monitors[$type] = array(NULL, NULL, NULL, TRUE);
						$this->detached($rec[0]);
					} else { // not monitored, just randomly cached
						unset($this->monitors[$type]);
					}
				}
			}

		} else { // attaching
			foreach ($this->monitors as $type => $rec) {
				if (isset($rec[0])) { // is in cache yet
					continue;

				} elseif (!$rec[3]) { // not monitored, just randomly cached
					unset($this->monitors[$type]);

				} elseif (isset($missing[$type])) { // known from previous lookup
					$this->monitors[$type] = array(NULL, NULL, NULL, TRUE);

				} else {
					$this->monitors[$type] = NULL; // means 'monitored' and forces re-lookup
					if ($this->lookup($type, FALSE) === NULL) {
						$missing[$type] = TRUE;
					}
				}
			}
		}
	}



	/**
	 * Sets the service location (experimental).
	 * @param  IServiceLocator
	 * @return void
	 */
	public function setServiceLocator(IServiceLocator $locator)
	{
		$this->serviceLocator = $locator;
	}



	/**
	 * Gets the service locator (experimental).
	 * @return IServiceLocator
	 */
	final public function getServiceLocator()
	{
		if ($this->serviceLocator === NULL) {
			$this->serviceLocator = $this->parent === NULL
				? Environment::getServiceLocator()
				: $this->parent->getServiceLocator();
		}

		return $this->serviceLocator;
	}



	/**
	 * Gets the service (experimental).
	 * @param  string
	 * @return object
	 */
	final public function getService($type)
	{
		return $this->getServiceLocator()->getService($type);
	}



	/********************* cloneable, serializable ****************d*g**/



	/**
	 * Object cloning.
	 */
	public function __clone()
	{
		if ($this->parent === NULL) {
			return;

		} elseif ($this->parent instanceof ComponentContainer) {
			$this->parent = $this->parent->isCloning();
			if ($this->parent === NULL) { // not cloning
				$this->refreshMonitors(0);
			}

		} else {
			$this->parent = NULL;
			$this->refreshMonitors(0);
		}
	}



	/**
	 * Prevents unserialization.
	 */
	final public function __wakeup()
	{
		throw new /*\*/NotImplementedException;
	}

}
