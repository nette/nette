<?php

/**
 * My Application
 */



/**
 * Feed channel presenter.
 */
class FeedPresenter extends BasePresenter
{

	/**
	 * @return void
	 */
	protected function startup()
	{
		// disables layout
		$this->changeLayout(FALSE);
	}

}
