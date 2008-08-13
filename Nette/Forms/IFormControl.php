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
 * @package    Nette::Forms
 * @version    $Id$
 */

/*namespace Nette::Forms;*/



/**
 * Defines method that must be implemented to allow a component to act like a form control.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Forms
 */
interface IFormControl
{

	/**
	 * Loads HTTP data.
	 * @param  array
	 * @return void
	 */
	function loadHttpData($data);

	/**
	 * Sets control's value.
	 * @param  mixed
	 * @return void
	 */
	function setValue($value);

	/**
	 * Returns control's value.
	 * @return mixed
	 */
	function getValue();

	/**
	 * @return Rules
	 */
	function getRules();

	/**
	 * Returns errors corresponding to control.
	 * @return array
	 */
	function getErrors();

	/**
	 * Is control disabled?
	 * @return bool
	 */
	function getDisabled();

	/**
	 * Returns translator adapter.
	 * @return ITranslator
	 */
	function getTranslator();

}
