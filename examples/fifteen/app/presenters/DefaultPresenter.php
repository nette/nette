<?php




class DefaultPresenter extends /*Nette::Application::*/Presenter
{


	public function prepareDefault()
	{
		require_once Environment::expand('%componentsDir%/FifteenControl.php');

		$fifteen = new FifteenControl($this, 'game');
		$fifteen->onGameOver[] = array($this, 'GameOver');
		$fifteen->useAjax = TRUE;

		$this->template->registerFilter(/*Nette::Application::*/'TemplateFilters::curlyBrackets');
		$this->template->fifteen = $fifteen;

		$this->invalidateControl('round');
	}



	public function GameOver($sender, $round)
	{
		$this->template->flash = 'Congratulate!';
		$this->invalidateControl('flash');
	}

}
