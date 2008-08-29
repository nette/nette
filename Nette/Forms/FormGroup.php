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



require_once dirname(__FILE__) . '/../Object.php';



/**
 * A user group of form controls.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Forms
 */
class FormGroup extends /*Nette::*/Object
{
	/** @var array */
	protected $controls = array();

	/** @var string */
	protected $legend;



	public function __construct($legend)
	{
		$this->legend = $legend;
	}



	/**
	 * @return void
	 */
	public function add()
	{
		foreach (func_get_args() as $id => $control) {
			if ($control instanceof IFormControl) {
				if (!in_array($control, $this->controls, TRUE)) {
					$this->controls[] = $control;
				}

			} else {
				throw new /*::*/InvalidArgumentException("Only IFormControl items are allowed, the #$id parameter is invalid.");
			}
		}
	}



	/**
	 * @return array
	 */
	public function getControls()
	{
		return $this->controls;
	}



	/**
	 * @param  string
	 * @return FormGroup  provides a fluent interface
	 */
	public function setLegend($legend)
	{
		$this->legend = $legend;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getLegend()
	{
		return $this->legend;
	}

}
