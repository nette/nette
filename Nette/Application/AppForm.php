<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com/
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com/
 * @category   Nette
 * @package    Nette::Application
 */

/*namespace Nette::Application;*/



require_once dirname(__FILE__) . '/../Forms/Form.php';

require_once dirname(__FILE__) . '/../Application/ISignalReceiver.php';



/**
 * Form - allows create, validate and render (X)HTML forms.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Application
 * @version    $Revision$ $Date$
 */
class AppForm extends /*Nette::Forms::*/Form implements ISignalReceiver
{

	/**
	 * Application form constructor.
	 */
	public function __construct(/*Nette::*/IComponentContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($name, $parent);
	}



	/**
	 * Returns the presenter where this component belongs to.
	 * @param  bool   throw exception if presenter doesn't exist?
	 * @return Presenter|NULL
	 */
	public function getPresenter($need = TRUE)
	{
		return $this->lookup('Nette::Application::Presenter', $need);
	}



	protected function notification(/*Nette::*/IComponent $sender, $message)
	{
		parent::notification($sender, $message);

		$presenter = $this->getPresenter(FALSE);
		if ($message === self::HIERARCHY_ATTACH && $presenter !== NULL) {
			$id = $this->lookupPath('Nette::Application::IPresenter');
			$presenter->registerComponent($id, $this);
			$this->setAction(new Link(
				$presenter,
				'this!',
				array(Presenter::SIGNAL_KEY => "$id-submit")
			));

		} elseif ($message === self::HIERARCHY_DETACH && $presenter !== NULL) {
			// is called before sender's parent is about to be detached
			$presenter->unregisterComponent($this);
		}
	}



	/**
	 * Detects form submission and loads PresenterRequest values.
	 * @return void
	 */
	protected function detectSubmission()
	{
		$this->submittedBy = FALSE;

		$presenter = $this->getPresenter();
		if ($presenter->getSignalReceiver() !== $this) return;

		$request = $presenter->getRequest();
		if ($request->isForward() || $request->isPost() !== $this->isPost) return;

		$this->submittedBy = TRUE;

		$request = $this->getPresenter()->getRequest();
		$data = $this->isPost ? (array) $request->getPost() + (array) $request->getFiles() : (array) $request->getParams();
		$this->loadHttpData($data);
	}



	/********************* interface ISignalReceiver ****************d*g**/



	/**
	 * This method is called by presenter.
	 * @return void
	 */
	public function handleSubmit()
	{
		if (!$this->isSubmitted() || ($this->onlyValid && !$this->isValid())) {
			return;
		}

		if ($this->submittedBy instanceof FormControl) {
			$this->submittedBy->Click();
		}

		$this->onSubmit($this);
	}



	/**
	 * This method is called by presenter.
	 * @param  string
	 * @return void
	 */
	public function signalReceived($signal)
	{
		if ($signal === 'submit') {
			if (!$this->isSubmitted() || ($this->onlyValid && !$this->isValid())) {
				return;
			}

			if ($this->submittedBy instanceof SubmitButton) {
				$this->submittedBy->Click();
			}

			$this->onSubmit($this);

		} else {
			throw new SignalException("There is no handler for signal '$signal' in '{$this->getClass()}'.");
		}
	}

}
