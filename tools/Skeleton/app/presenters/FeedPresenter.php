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
	protected function beforeRender()
	{
		// disables layout
		$this->setLayout(FALSE);
	}



	public function renderRss()
	{
		$this->template->title = 'My feed';
		$this->template->description = 'The latest news';

		$this->template->items = array();
		$this->template->items[] = (object) array(
			'title' => 'An article',
		);
		$this->template->items[] = (object) array(
			'title' => 'Another article',
			'link' => $this->link('//Homepage:'),
		);
	}

}
