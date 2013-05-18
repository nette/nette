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

	/** @var int */
	private $timeout;

	/** @var Nette\Http\Session */
	public $session;



	/**
	 * @param string
	 * @param int
	 */
	public function __construct($message, $timeout)
	{
		parent::__construct();
		$this->timeout = $timeout;
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
		$key = 'key' . $this->timeout;
		$session = $this->getSession()->getSection(__CLASS__);
		$session->setExpiration($this->timeout, $key);
		if (!isset($session->$key)) {
			$session->$key = Nette\Utils\Strings::random();
		}
		return $session->$key;
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
		return (string) $control->getValue() === (string) $control->getToken();
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
