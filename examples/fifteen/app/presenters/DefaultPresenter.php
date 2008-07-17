<?php




class DefaultPresenter extends /*Nette::Application::*/Presenter
{
	/** @var FifteenControl */
	public $fifteen;



	public function prepareDefault()
	{
		require_once Environment::expand('%componentsDir%/FifteenControl.php');

		$this->fifteen = new FifteenControl($this, 'game');
		$this->fifteen->onGameOver[] = array($this, 'GameOver');
		$this->fifteen->useAjax = TRUE;

		$this->template->registerFilter(/*Nette::Application::*/'TemplateFilters::curlyBrackets');
		$this->template->fifteen = $this->fifteen;

		$this->invalidatePartial('round');
	}



	public function GameOver($sender, $round)
	{
		$this->template->flash = 'Congratulate!';
		$this->invalidatePartial('flash');
	}

}
