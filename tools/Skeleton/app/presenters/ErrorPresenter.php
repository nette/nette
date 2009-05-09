<?php

/**
 * My Application
 *
 * @copyright  Copyright (c) 2009 John Doe
 * @package    MyApplication
 * @version    $Id$
 */



/**
 * Error presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class ErrorPresenter extends BasePresenter
{

	/**
	 * @return void
	 */
	public function renderDefault($exception)
	{
		if ($this->isAjax()) {
			$this->getPayload()->events[] = array('error', $exception->getMessage());
			$this->terminate();

		} else {
			$this->template->robots = 'noindex,noarchive';

			if ($exception instanceof /*Nette\Application\*/BadRequestException) {
				Environment::getHttpResponse()->setCode($exception->getCode());
				$this->template->title = '404 Not Found';
				$this->setView('404');

			} else {
				Environment::getHttpResponse()->setCode(500);
				$this->template->title = '500 Internal Server Error';
				$this->setView('500');

				/*Nette\*/Debug::processException($exception);
			}
		}
	}

}
