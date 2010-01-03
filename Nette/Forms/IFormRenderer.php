<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Forms
 */

/*namespace Nette\Forms;*/



/**
 * Defines method that must implement form rendered.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Forms
 */
interface IFormRenderer
{

	/**
	 * Provides complete form rendering.
	 * @param  Form
	 * @return string
	 */
	function render(Form $form);

}
