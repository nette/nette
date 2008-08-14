<?php

/**
 * My Application
 */



/**
 * Error presenter.
 */
class ErrorPresenter extends BasePresenter
{

	/**
	 * @return void
	 */
	public function renderDefault($exception)
	{
		if ($this->isAjax()) {
			$this->getAjaxDriver()->fireEvent('error', $exception->getMessage());
			$this->terminate();

		} else {
			$this->template->robots = 'noindex,noarchive';

			if ($exception instanceof /*Nette::Application::*/BadRequestException) {
				Environment::getHttpResponse()->setCode(404);
				$this->template->title = '404 Not Found';
				$this->template->message = 'The requested URL was not found on this server.';

			} else {
				Environment::getHttpResponse()->setCode(500);
				$this->template->title = '500 Internal Server Error';
				$this->template->message = 'The server encountered an internal error and was unable to complete your request.';
			}
		}
	}

}
