<?php

/**
 * My Application
 *
 * @copyright  Copyright (c) 2009 John Doe
 * @package    MyApplication
 * @version    $Id$
 */



/**
 * Feed channel presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class FeedPresenter extends BasePresenter
{

	/**
	 * @return void
	 */
	protected function startup()
	{
		// disables layout
		$this->setLayout(FALSE);
	}

}
