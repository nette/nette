<?php


abstract class BasePresenter extends /*Nette::Application::*/Presenter
{

	protected function beforeRender()
	{
		$this->template->registerFilter(/*Nette::Application::*/'TemplateFilters::curlyBrackets');

		$user = Environment::getUser();
		$this->template->user = $user->isAuthenticated() ? $user->getIdentity() : NULL;
	}

}
