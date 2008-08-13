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



require_once dirname(__FILE__) . '/../../Forms/Controls/FormControl.php';



/**
 * Push button control with no default behavior.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Forms
 */
class Button extends FormControl
{

	/**
	 * @param  string  label
	 */
	public function __construct($label)
	{
		parent::__construct(NULL);
		$this->control->type = 'button';
		$this->control->value = $label;
		$this->value = FALSE;
		$this->setHtmlId(FALSE);
	}



	/**
	 * Bypasses label generation.
	 * @return void
	 */
	public function getLabel()
	{
		return NULL;
	}



	/**
	 * Sets 'pressed' indicator.
	 * @param  bool
	 * @return void
	 */
	public function setValue($value)
	{
		$this->value = (bool) $value;
	}

}
