<?php

use Nette\Environment,
	Nette\Application\Presenter;


abstract class BasePresenter extends Presenter
{

	protected function beforeRender()
	{
		$user = Environment::getUser();
		$this->template->user = $user->isLoggedIn() ? $user->getIdentity() : NULL;
	}

}
