<?php

/*use Nette::Environment;*/
/*use Nette::Security::AuthenticationException;*/

require_once dirname(__FILE__) . '/BasePresenter.php';


class AuthPresenter extends BasePresenter
{
	/** @persistent */
	public $backlink = '';


	protected function startup()
	{
		require_once 'models/Users.php';

		parent::startup();
	}



	/********************* view Default *********************/



	public function prepareDefault()
	{
		$this->forward('login');
	}



	/********************* view Login *********************/



	public function renderLogin($backlink)
	{
		$this->backlink = $backlink;
		$this->template->title = "Log in";
	}



	public function handleLogin()
	{
		$request = $this->request;
		if (!$request->isPost()) return;

		// collect the data from the user
		$username = trim($request->post['username']);
		$password = trim($request->post['password']);

		if (empty($username)) {
			$this->template->message = 'Please provide a username.';
		} else {
			try {
				$user = Environment::getUser();
				$user->authenticate($username, $password);
				$this->redirect($this->backlink);

			} catch (AuthenticationException $e) {
				$this->template->message = 'Login failed.';
			}
		}
	}

}
