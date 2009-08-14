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
 */

/*namespace Nette;*/



/**
 * Provides functionality required by all components.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette
 */
interface IComponent
{
	/** Separator for component names in path concatenation. */
	const NAME_SEPARATOR = '-';

	/**
	 * @return string
	 */
	function getName();

	/**
	 * Returns the container if any.
	 * @return IComponentContainer|NULL
	 */
	function getParent();

	/**
	 * Sets the parent of this component.
	 * @param  IComponentContainer
	 * @param  string
	 * @return void
	 */
	function setParent(IComponentContainer $parent = NULL, $name = NULL);

	/**
	 * Sets the service location (EXPERIMENTAL).
	 * @param  IServiceLocator
	 * @return void
	 */
	function setServiceLocator(IServiceLocator $locator);

	/**
	 * Gets the service locator (EXPERIMENTAL).
	 * @return IServiceLocator
	 */
	function getServiceLocator();

}
