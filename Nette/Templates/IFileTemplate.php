<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Templates;

use Nette;



/**
 * Defines file-based template methods.
 *
 * @author     David Grudl
 */
interface IFileTemplate extends ITemplate
{

	/**
	 * Sets the path to the template file.
	 * @param  string  template file path
	 * @return void
	 */
	function setFile($file);

	/**
	 * Returns the path to the template file.
	 * @return string  template file path
	 */
	function getFile();

}
