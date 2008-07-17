<?php

/**
 * My Application
 */



/**
 * Error presenter.
 */
class ErrorPresenter extends BasePresenter
{

	public function renderDefault()
	{
        $this->template->title = 'An error occurred';
	}

}
