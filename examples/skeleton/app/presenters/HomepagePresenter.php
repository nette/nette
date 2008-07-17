<?php

/**
 * My Application
 */



/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

	public function renderDefault()
	{
		$this->template->title = 'It works!';
	}

}
