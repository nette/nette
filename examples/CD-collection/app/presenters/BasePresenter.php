<?php

/*use Nette\Environment;*/
/*use Nette\Application\Presenter;*/


abstract class BasePresenter extends Presenter
{
	public $oldLayoutMode = FALSE;


	protected function beforeRender()
	{
		$user = Environment::getUser();
		$this->template->user = $user->isAuthenticated() ? $user->getIdentity() : NULL;
	}

}
