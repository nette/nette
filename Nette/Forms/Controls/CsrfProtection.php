<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
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
			$session->token = Nette\Utils\Random::generate();
		}
		return $session->token;
	}


	/**
	 * @return string
	 */
	private function generateToken($random = NULL)
	{
		if ($random === NULL) {
			$random = Nette\Utils\Random::generate(10);
		}
		return $random . base64_encode(sha1($this->getToken() . $random, TRUE));
	}


	/**
	 * Generates control's HTML element.
	 *
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		return parent::getControl()->value($this->generateToken());
	}


	/**
	 * @return bool
	 */
	public static function validateCsrf(CsrfProtection $control)
	{
		$value = $control->getValue();
		return $control->generateToken(substr($value, 0, 10)) === $value;
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
