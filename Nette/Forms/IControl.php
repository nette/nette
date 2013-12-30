<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette;


/**
 * Defines method that must be implemented to allow a component to act like a form control.
 *
 * @author     David Grudl
 */
interface IControl
{

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
	 * @return void
	 */
	function validate();

	/**
	 * Returns errors corresponding to control.
	 * @return array
	 */
	function getErrors();

	/**
	 * Is control value excluded from $form->getValues() result?
	 * @return bool
	 */
	function isOmitted();

	/**
	 * Returns translated string.
	 * @param  string
	 * @param  int      plural count
	 * @return string
	 */
	function translate($s, $count = NULL);

}
