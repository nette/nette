<?php

/*use Nette\Environment;*/
/*use Nette\Application\Presenter;*/


abstract class BasePresenter extends Presenter
{

	protected function beforeRender()
	{
		$this->template->registerFilter('Nette\Templates\CurlyBracketsFilter::invoke');

		$user = Environment::getUser();
		$this->template->user = $user->isAuthenticated() ? $user->getIdentity() : NULL;
	}

}
