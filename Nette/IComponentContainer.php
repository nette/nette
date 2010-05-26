<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette
 */

namespace Nette;

use Nette;



/**
 * Containers are objects that logically contain zero or more IComponent components.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette
 */
interface IComponentContainer extends IComponent
{

	/**
	 * Adds the specified component to the IComponentContainer.
	 * @param  IComponent
	 * @param  string
	 * @return void
	 */
	function addComponent(IComponent $component, $name);

	/**
	 * Removes a component from the IComponentContainer.
	 * @param  IComponent
	 * @return void
	 */
	function removeComponent(IComponent $component);

	/**
	 * Returns single component.
	 * @param  string
	 * @return IComponent|NULL
	 */
	function getComponent($name);

	/**
	 * Iterates over a components.
	 * @param  bool    recursive?
	 * @param  string  class types filter
	 * @return \ArrayIterator
	 */
	function getComponents($deep = FALSE, $filterType = NULL);

}
