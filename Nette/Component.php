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


require_once dirname(__FILE__) . '/IComponent.php';

require_once dirname(__FILE__) . '/Object.php';



/**
 * Component is the base class for all components.
 *
 * Components are objects implementing IComponent. They has parent component,
 * own name and service locator.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette
 */
abstract class Component extends Object implements IComponent
{
	const NAME_SEPARATOR = '-';

	const HIERARCHY_ATTACH = 1;

	const HIERARCHY_DETACH = 2;

	/** @var IServiceLocator */
	private $serviceLocator;

	/** @var IComponentContainer */
	private $parent;

	/** @var string */
	private $name;

	/** @var array */
	private $lookupCache = array();




	/**
	 */
	public function __construct(IComponentContainer $parent = NULL, $name = NULL)
	{
		if ($parent !== NULL) {
			$parent->addComponent($this, $name);

		} elseif (is_string($name)) {
			$this->name = $name;
		}

		$this->constructed();
	}



	/**
	 * This method will be called from component constructor.
	 * @return void
	 */
	protected function constructed()
	{
	}



	/**
	 * Lookup hierarchy for object specified by class or interface name.
	 * @param  string
	 * @return IComponent
	 */
	public function lookup($type)
	{
		/**/// fix for namespaced classes/interfaces in PHP < 5.3
		if ($a = strrpos($type, ':')) $type = substr($type, $a + 1);/**/

		if (isset($this->lookupCache[$type])) {
			return $this->lookupCache[$type][0];
		}

		$obj = $this;
		$path = array();
		do {
			if ($obj instanceof $type) break;
			array_unshift($path, $obj->getName());
			$obj = $obj->getParent(); // IConponent::getParent()
			if ($obj === $this) $obj = NULL; // prevent cycling
		} while ($obj !== NULL);

		$this->lookupCache[$type] = array(
			$obj,
			$obj === NULL ? NULL : implode(self::NAME_SEPARATOR, $path),
		);

		return $this->lookupCache[$type][0];
	}



	/**
	 * Lookup for object specified by class or interface name. Returns backtrace path.
	 * A path is the concatenation of component names separated by self::NAME_SEPARATOR.
	 * @param  string
	 * @return string
	 */
	public function lookupPath($type)
	{
		/**/// fix for namespaced classes/interfaces in PHP < 5.3
		if ($a = strrpos($type, ':')) $type = substr($type, $a + 1);/**/

		if (isset($this->lookupCache[$type])) {
			return $this->lookupCache[$type][1];
		} else {
			$this->lookup($type);
			return $this->lookupCache[$type][1];
		}
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
	 * @throws ::InvalidStateException
	 */
	public function setParent(IComponentContainer $parent = NULL, $name = NULL)
	{
		// if parent is the same parent it already has, no action occurs (even name or service change)
		if ($this->parent === $parent) return;

		// A component cannot be given a parent if it already has a parent.
		if ($this->parent !== NULL && $parent !== NULL) {
			throw new /*::*/InvalidStateException('Component already has a parent.');
		}

		// remove from parent?
		if ($parent === NULL) {
			// parent cannot be removed if is still this component contains
			if ($this->parent->getComponent($this->name) === $this) {
				throw new /*::*/InvalidStateException('The current parent still recognizes this component as its child.');
			}

			$this->notification($this, self::HIERARCHY_DETACH);
			$this->parent = NULL;
			$this->refreshCache();

		} else { // add to parent
			// Given parent container does not already recognize this component as its child.
			if ($parent->getComponent($name) !== $this) {
				throw new /*::*/InvalidStateException('The given parent does not recognize this component as its child.');
			}

			$this->validateParent($parent);

			$this->parent = $parent;
			if ($name !== NULL) $this->name = $name;
			$this->refreshCache();
			$this->notification($this, self::HIERARCHY_ATTACH);
		}
	}



	/**
	 * Is called by a component when it is about to be set new parent. Descendant can.
	 * override this method to disallow a parent change by throwing an ::InvalidStateException
	 * @param  IComponentContainer
	 * @return void
	 * @throws ::InvalidStateException
	 */
	protected function validateParent(IComponentContainer $parent)
	{
	}



	/**
	 * Forwards notification messages to all components in hierarchy. Do not call directly.
	 * @param  IComponent
	 * @param  mixed
	 * @return void
	 */
	protected function notification(IComponent $sender, $message)
	{
		if ($this instanceof IComponentContainer) {
			foreach ($this->getComponents() as $component) {
				if ($component instanceof Component) { // or move to interface?
					$component->notification($sender, $message);
				}
			}
		}
	}



	/**
	 * Refresh lookup cache (don't call directly).
	 * @param  IComponent
	 * @return void
	 */
	private function refreshCache()
	{
		$this->lookupCache = array();

		if ($this instanceof IComponentContainer) {
			foreach ($this->getComponents() as $component) {
				if ($component instanceof self) {
					$component->refreshCache();
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
		if ($this->parent !== NULL &&
			!($this->parent instanceof ComponentContainer && $this->parent->isCloning()))
		{
			$this->setParent(NULL);
		}
	}



	/**
	 * Prevents serialization.
	 */
	final public function __sleep()
	{
		throw new /*::*/NotImplementedException;
	}

}
