<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Forms;

use Nette;



/**
 * Defines method that must be implemented to allow a component to act like a form control.
 *
 * @author     David Grudl
 */
interface IFormControl
{

	/**
	 * Loads HTTP data.
	 * @return void
	 */
	function loadHttpData();

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
	function isDisabled();

	/**
	 * Returns translated string.
	 * @param  string
	 * @param  int      plural count
	 * @return string
	 */
	function translate($s, $count = NULL);

}
