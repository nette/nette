<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Forms
 */

namespace Nette\Forms;

use Nette;



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
