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
 * Instant validation JavaScript generator.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Forms
 */
final class InstantClientScript extends Nette\Object
{
	/** @var Form */
	private $form;



	public function __construct(Form $form)
	{
		$this->form = $form;
	}



	public function enable()
	{
	}



	/**
	 * Generates the client side validation script.
	 * @return string
	 */
	public function renderClientScript()
	{
		foreach ($this->form->getControls() as $control) {
			if ($control->getRules()) {
				$formName = Nette\Json::encode((string) $this->form->getElementPrototype()->id);
				ob_start();
				require __DIR__ . '/InstantClientScript.phtml';
				return ob_get_clean();
			}
		}
	}



	public static function javascript()
	{
		return TRUE;
	}

}
