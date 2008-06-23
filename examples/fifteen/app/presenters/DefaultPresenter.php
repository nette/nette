<?php




class DefaultPresenter extends /*Nette::Application::*/Presenter
{
	/** @var string */
	public $flash;

	/** @var FifteenControl */
	public $fifteen;



	public function prepareDefault()
	{
		require_once dirname(__FILE__) . '/../components/FifteenControl.php';

		$this->fifteen = new FifteenControl($this, 'game');
		$this->fifteen->onGameOver[] = array($this, 'GameOver');
		$this->fifteen->useAjax = TRUE;

		$this->template->registerFilter(/*Nette::Application::*/'TemplateFilters::curlyBrackets');
	}



	public function GameOver($sender, $round)
	{
		$this->flash = 'Congratulate!';
	}


}
