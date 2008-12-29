<?php


abstract class BasePresenter extends /*Nette\Application\*/Presenter
{

	protected function beforeRender()
	{
		$this->template->registerFilter('Nette\Templates\CurlyBracketsFilter::invoke');

		$user = Environment::getUser();
		$this->template->user = $user->isAuthenticated() ? $user->getIdentity() : NULL;
	}

}
