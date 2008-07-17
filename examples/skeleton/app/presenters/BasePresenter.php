<?php

/**
 * My Application
 */



/**
 * Base class for all application presenters.
 */
abstract class BasePresenter extends /*Nette::Application::*/Presenter
{

	/**
	 * @return void
	 */
	protected function beforeRender()
	{
		$this->template->registerFilter(/*Nette::Application::*/'TemplateFilters::curlyBrackets');
	}

}
