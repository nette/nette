<?php


abstract class BasePresenter extends /*Nette\Application\*/Presenter
{

	protected function beforeRender()
	{
		$this->template->registerFilter(/*Nette\Templates\*/'CurlyBracketsFilter::invoke');
		$this->template->view = $this->view;
		$a = strrpos($this->name, ':');
		if ($a === FALSE) {
			$this->template->module = '';
			$this->template->presenter = $this->name;
		} else {
			$this->template->module = substr($this->name, 0, $a + 1);
			$this->template->presenter = substr($this->name, $a + 1);
		}
	}

}
