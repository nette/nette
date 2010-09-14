<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette;

use Nette;



/**
 * Containers are objects that logically contain zero or more IComponent components.
 *
 * @author     David Grudl
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
	 * @return \Iterator
	 */
	function getComponents($deep = FALSE, $filterType = NULL);

}
