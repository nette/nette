<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Templates;

use Nette;



/**
 * Defines template methods.
 *
 * @author     David Grudl
 */
interface ITemplate
{

	/**
	 * Renders template to output.
	 * @return void
	 */
	function render();

}
