<?php

/**
 * My Application
 *
 * @copyright  Copyright (c) 2010 John Doe
 * @package    MyApplication
 */

use Nette\Environment,
	Nette\Application\AppForm,
	Nette\Forms\Form,
	Nette\Security\AuthenticationException;


/**
 * Login / logout presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class LoginPresenter extends BasePresenter
{


	/**
	 * Login form component factory.
	 * @return mixed
	 */
	protected function createComponentLoginForm()
	{
		$form = new AppForm;
		$form->addText('username', 'Username:')
			->addRule(Form::FILLED, 'Please provide a username.');

		$form->addPassword('password', 'Password:')
			->addRule(Form::FILLED, 'Please provide a password.');

		$form->addCheckbox('remember', 'Remember me on this computer');

		$form->addSubmit('login', 'Login');

		$form->onSubmit[] = callback($this, 'loginFormSubmitted');
		return $form;
	}



	public function loginFormSubmitted($form)
	{
		try {
			$values = $form->values;
			if ($values['remember']) {
				$this->getUser()->setExpiration('+ 14 days', FALSE);
			} else {
				$this->getUser()->setExpiration('+ 20 minutes', TRUE);
			}
			$this->getUser()->login($values['username'], $values['password']);
			$this->redirect('Homepage:');

		} catch (AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}

}
