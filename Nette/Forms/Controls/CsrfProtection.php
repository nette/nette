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

use Nette,
	Nette\Application\UI\Presenter,
	Nette\Http\Session,
	Nette\Forms\Form;



/**
 * @author Filip Procházka
 */
class CsrfProtection extends HiddenField
{
	const PROTECTION = 'Nette\Forms\Controls\CsrfProtection::validateCsrf';

	/** @var string */
	private $token;

	/** @var int */
	private $timeout;

	/** @var Nette\Http\IRequest */
	private $httpRequest;

	/** @var Nette\Http\Session */
	private $session;



	/**
	 * @param string
	 * @param int
	 */
	public function __construct($message, $timeout)
	{
		parent::__construct();
		$this->timeout = $timeout;
		$this->addRule(self::PROTECTION, $message);
		$this->monitor('Nette\Application\UI\Presenter');
	}



	protected function attached($parent)
	{
		parent::attached($parent);

		if ($parent instanceof Presenter && !$this->session) {
			$this->session = $parent->getContext()->getByType('Nette\Http\Session');
		}
	}



	/**
	 * @return string
	 */
	protected function getToken()
	{
		if ($this->token !== NULL) {
			return $this->token;
		}

		$session = $this->getSession()->getSection('Nette.Forms.Form/CSRF');
		$key = "key" . $this->timeout;
		if (isset($session->$key)) {
			$this->token = $session->$key;

		} else {
			$session->$key = $this->token = Nette\Utils\Strings::random();
		}

		$session->setExpiration($this->timeout, $key);
		return $this->token;
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
	 * @param CsrfProtection $control
	 * @return bool
	 */
	public static function validateCsrf(CsrfProtection $control)
	{
		return (string) $control->getValue() === (string) $control->getToken();
	}



	/********************* backend ****************d*g**/



	/**
	 * @internal
	 * @param \Nette\Http\IRequest
	 */
	public function injectHttpRequest(Nette\Http\IRequest $httpRequest)
	{
		if ($this->httpRequest) {
			throw new Nette\InvalidStateException('Service Nette\Http\IRequest has already been set.');
		}
		$this->httpRequest = $httpRequest;
	}



	/**
	 * @param \Nette\Http\Session $session
	 */
	public function injectSession(Session $session)
	{
		if ($this->session) {
			throw new Nette\InvalidStateException('Service Nette\Http\Session has already been set.');
		}
		$this->session = $session;
	}



	/**
	 * @return \Nette\Http\Session
	 */
	protected function getSession()
	{
		if (!$this->session) {
			$this->session = new Session($this->httpRequest, new Nette\Http\Response);
		}
		return $this->session;
	}

}
