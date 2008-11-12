<?php

/**
 * My Application
 *
 * @copyright  Copyright (c) 2008 John Doe
 * @package    MyApplication
 * @version    $Id$
 */



/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class HomepagePresenter extends BasePresenter
{

	public function renderDefault()
	{
		$this->template->title = 'It works!';
	}

}
