<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Forms\Controls;

use Nette;


/**
 * @author Filip Procházka
 */
class CsrfProtection extends HiddenField
{
	const PROTECTION = 'Nette\Forms\Controls\CsrfProtection::validateCsrf';

	/** @var Nette\Http\Session */
	public $session;


	/**
	 * @param string
	 * @param int
	 */
	public function __construct($message)
	{
		parent::__construct();
		$this->setOmitted()->addRule(self::PROTECTION, $message);
		$this->monitor('Nette\Application\UI\Presenter');
	}


	protected function attached($parent)
	{
		parent::attached($parent);
		if (!$this->session && $parent instanceof Nette\Application\UI\Presenter) {
			$this->session = $parent->getSession();
		}
	}


	/**
	 * @return string
	 */
	public function getToken()
	{
		$session = $this->getSession()->getSection(__CLASS__);
		if (!isset($session->token)) {
			$session->token = Nette\Utils\Strings::random();
		}
		return $session->token;
	}


	/**
	 * Generates control's HTML element.
	 *
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		return parent::getControl()->value($this->getToken());
	}


	/**
	 * @return bool
	 */
	public static function validateCsrf(CsrfProtection $control)
	{
		return $control->getValue() === $control->getToken();
	}


	/********************* backend ****************d*g**/


	/**
	 * @return Nette\Http\Session
	 */
	private function getSession()
	{
		if (!$this->session) {
			$this->session = new Nette\Http\Session($this->getForm()->httpRequest, new Nette\Http\Response);
		}
		return $this->session;
	}

}
