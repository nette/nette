<?php


abstract class BasePresenter extends /*Nette::Application::*/Presenter
{

	protected function startup()
	{
		$this->template->registerFilter('TemplateFilters::curlyBrackets');
	}

}
