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
 * Hidden form control used to store a non-displayed value.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Forms
 */
class HiddenField extends FormControl
{
	/** @var string */
	private $forcedValue;



	public function __construct($forcedValue = NULL)
	{
		parent::__construct(NULL);
		$this->control->type = 'hidden';
		$this->value = (string) $forcedValue;
		$this->forcedValue = $forcedValue;
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
	 * Sets control's value.
	 * @param  string
	 * @return void
	 */
	public function setValue($value)
	{
		$this->value = is_scalar($value) ? (string) $value : '';
	}



	/**
	 * Generates control's HTML element.
	 * @return Nette::Web::Html
	 */
	public function getControl()
	{
		return parent::getControl()->value($this->forcedValue === NULL ? $this->value : $this->forcedValue);
	}

}
