<?php


abstract class BasePresenter extends /*Nette::Application::*/Presenter
{

	protected function beforeRender()
	{
		$this->template->registerFilter(/*Nette::Application::*/'TemplateFilters::curlyBrackets');

		$this->template->view = $this->view;
		$presenter = $this->request->presenterName;
		$a = strrpos($presenter, ':');
		if ($a === FALSE) {
			$this->template->module = '';
			$this->template->presenter = $presenter;
		} else {
			$this->template->module = substr($presenter, 0, $a + 1);
			$this->template->presenter = substr($presenter, $a + 1);
		}
	}

}
