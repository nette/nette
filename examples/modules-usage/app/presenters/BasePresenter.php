<?php


abstract class BasePresenter extends Nette\Application\Presenter
{

	protected function beforeRender()
	{
		$this->template->viewName = $this->view;
		$this->template->root = dirname(APP_DIR);

		$a = strrpos($this->name, ':');
		if ($a === FALSE) {
			$this->template->moduleName = '';
			$this->template->presenterName = $this->name;
		} else {
			$this->template->moduleName = substr($this->name, 0, $a + 1);
			$this->template->presenterName = substr($this->name, $a + 1);
		}
	}

}
