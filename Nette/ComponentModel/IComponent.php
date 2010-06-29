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
 * Provides functionality required by all components.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
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

}
