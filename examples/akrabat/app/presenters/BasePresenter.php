<?php


abstract class BasePresenter extends /*Nette::Application::*/Presenter
{

	protected function beforeRender()
	{
		$this->template->registerFilter(/*Nette::Templates::*/'TemplateFilters::curlyBrackets');

		$user = Environment::getUser();
		$this->template->user = $user->isAuthenticated() ? $user->getIdentity() : NULL;
	}

}
