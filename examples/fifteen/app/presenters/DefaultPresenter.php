<?php




class DefaultPresenter extends /*Nette\Application\*/Presenter
{


	public function renderDefault()
	{
		$this->invalidateControl('round');
	}



	/**
	 * Fifteen game control factory.
	 * @return mixed
	 */
	protected function createComponentFifteen()
	{
		$fifteen = new FifteenControl;
		$fifteen->onGameOver[] = array($this, 'gameOver');
		return $fifteen;
	}



	public function gameOver($sender, $round)
	{
		$this->template->flash = 'Congratulate!';
		$this->invalidateControl('flash');
	}

}
