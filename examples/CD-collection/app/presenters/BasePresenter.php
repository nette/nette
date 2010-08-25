<?php

use Nette\Application\Presenter;


abstract class BasePresenter extends Presenter
{
	public $oldLayoutMode = FALSE;


	protected function beforeRender()
	{
		$this->getSession()->start();
		$user = $this->getUser();
		$this->template->user = $user->isLoggedIn() ? $user->getIdentity() : NULL;
	}

}
