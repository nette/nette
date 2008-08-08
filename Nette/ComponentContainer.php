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



require_once dirname(__FILE__) . '/Component.php';

require_once dirname(__FILE__) . '/IComponentContainer.php';



/**
 * ComponentContainer is default implementation of IComponentContainer.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette
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
	 * @param  string
	 * @return void
	 * @throws ::InvalidStateException
	 */
	public function addComponent(IComponent $component, $name, $placeBefore = NULL)
	{
		if ($name === NULL) {
			$name = $component->getName();
		}

		if ($name == NULL) { // intentionally ==
			throw new /*::*/InvalidArgumentException('Component name is required.');
		}

		if (!is_string($name) || !preg_match('#^[a-zA-Z0-9_]+$#', $name)) {
			throw new /*::*/InvalidArgumentException("Component name must be non-empty alphanumeric string, '$name' is invalid.");
		}

		if (isset($this->components[$name])) {
			throw new /*::*/InvalidStateException("Component with name '$name' already exists.");
		}

		// check circular reference
		$obj = $this;
		do {
			if ($obj === $component) {
				throw new /*::*/InvalidStateException("Circular reference detected.");
			}
			$obj = $obj->getParent();
		} while ($obj !== NULL);

		// user checking
		$this->validateChildComponent($component);

		try {
			if (isset($this->components[$placeBefore])) {
				$tmp = array();
				foreach ($this->components as $k => $v) {
					if ($k === $placeBefore) $tmp[$name] = $component;
					$tmp[$k] = $v;
				}
				$this->components = $tmp;
			} else {
				$this->components[$name] = $component;
			}
			$component->setParent($this, $name);

		} catch (/*::*/Exception $e) {
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
			throw new /*::*/InvalidArgumentException("Component named '$name' is not located in this container.");
		}

		unset($this->components[$name]);
		$component->setParent(NULL);
	}



	/**
	 * Returns component specified by name.
	 * @param  string
	 * @param  bool   throw exception if component doesn't exist?
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
	 * Iterates over a components.
	 * @param  bool    recursive?
	 * @param  string  class types filter
	 * @return ::ArrayIterator
	 */
	final public function getComponents($deep = FALSE, $type = NULL)
	{
		$iterator = new RecursiveComponentIterator($this->components);
		if ($deep) {
			$deep = $deep > 0 ? /*::*/RecursiveIteratorIterator::SELF_FIRST : /*::*/RecursiveIteratorIterator::CHILD_FIRST;
			$iterator = new /*::*/RecursiveIteratorIterator($iterator, $deep);
		}
		if ($type) {
			$iterator = new InstanceFilterIterator($iterator, $type);
		}
		return $iterator;
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






/**
 * Recursive component iterator. See ComponentContainer::getComponents().
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette
 */
class RecursiveComponentIterator extends /*::*/RecursiveArrayIterator
{

	/**
	 * Has the current element has children?
	 * @return bool
	 */
	public function hasChildren()
	{
		return $this->current() instanceof IComponentContainer;
	}



	/**
	 * The sub-iterator for the current element.
	 * @return ::RecursiveIterator
	 */
	public function getChildren()
	{
		return $this->current()->getComponents();
	}

}
