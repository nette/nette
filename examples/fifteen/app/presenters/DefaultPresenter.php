<?php




class DefaultPresenter extends /*Nette\Application\*/Presenter
{


	public function prepareDefault()
	{
		$fifteen = new FifteenControl;
		$fifteen->onGameOver[] = array($this, 'gameOver');

		$this->addComponent($fifteen, 'game');

		$this->template->registerFilter('Nette\Templates\CurlyBracketsFilter::invoke');
		$this->template->fifteen = $fifteen;

		$this->invalidateControl('round');
	}



	public function gameOver($sender, $round)
	{
		$this->template->flash = 'Congratulate!';
		$this->invalidateControl('flash');
	}

}
