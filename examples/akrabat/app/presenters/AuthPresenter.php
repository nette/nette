<?php

/*use Nette\Environment;*/
/*use Nette\Security\AuthenticationException;*/

require_once dirname(__FILE__) . '/BasePresenter.php';


class AuthPresenter extends BasePresenter
{
	/** @persistent */
	public $backlink = '';



	public function presentLogin($backlink)
	{
		if ($this->request->isMethod('post')) {
			// collect the data from the user
			$username = trim($this->request->post['username']);
			$password = trim($this->request->post['password']);

			if (empty($username)) {
				$this->template->message = 'Please provide a username.';
			} else {
				try {
					require_once 'models/Users.php';
					$user = Environment::getUser();
					$user->authenticate($username, $password);
					$this->getApplication()->restoreRequest($backlink);
					$this->redirect('Dashboard:');

				} catch (AuthenticationException $e) {
					$this->template->message = 'Login failed.';
				}
			}
		}

		$this->template->title = "Log in";
	}

}
