<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Templates
 */

/*namespace Nette\Templates;*/



/**
 * Defines file-based template methods.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Templates
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
