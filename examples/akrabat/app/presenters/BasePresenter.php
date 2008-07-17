<?php


abstract class BasePresenter extends /*Nette::Application::*/Presenter
{

	protected function startup()
	{
		$this->template->registerFilter(/*Nette::Application::*/'TemplateFilters::curlyBrackets');
	}

}
