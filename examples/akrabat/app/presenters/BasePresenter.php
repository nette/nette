<?php


abstract class BasePresenter extends Presenter
{

	protected function startup()
	{
		$this->template->registerFilter('TemplateFilters::curlyBrackets');
	}

}
