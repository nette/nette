<?php

/**
 * My Application
 *
 * @copyright  Copyright (c) 2009 John Doe
 * @package    MyApplication
 */

/*use Nette\Debug, Nette\Application\BadRequestException;*/



/**
 * Error presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class ErrorPresenter extends BasePresenter
{

	/**
	 * @param  Exception
	 * @return void
	 */
	public function renderDefault($exception)
	{
		if ($this->isAjax()) { // AJAX request? Just note this error in payload.
			$this->getPayload()->error = TRUE;
			$this->terminate();

		} elseif ($exception instanceof BadRequestException) {
			$this->template->title = '404 Not Found';
			$this->setView('404'); // load template 404.phtml

		} else {
			$this->template->title = '500 Internal Server Error';
			$this->setView('500'); // load template 500.phtml
			Debug::processException($exception); // and handle error by Nette\Debug
		}
	}

}
