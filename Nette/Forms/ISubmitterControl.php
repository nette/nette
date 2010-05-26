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
 * Defines method that must be implemented to allow a control to submit web form.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Forms
 */
interface ISubmitterControl extends IFormControl
{

	/**
	 * Tells if the form was submitted by this button.
	 * @return bool
	 */
	function isSubmittedBy();

	/**
	 * Gets the validation scope. Clicking the button validates only the controls within the specified scope.
	 * @return mixed
	 */
	function getValidationScope();

}