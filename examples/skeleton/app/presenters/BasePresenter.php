<?php

/**
 * My Application
 */



/**
 * Base class for all application presenters.
 */
abstract class BasePresenter extends /*Nette::Application::*/Presenter
{

	protected function startup()
	{
		$this->template->registerFilter(/*Nette::Application::*/'TemplateFilters::curlyBrackets');
	}

}
