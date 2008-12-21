<?php

/*use Nette\Environment;*/

require_once dirname(__FILE__) . '/BasePresenter.php';


class DashboardPresenter extends BasePresenter
{

	protected function startup()
	{
		// user authentication
		$user = Environment::getUser();
		if (!$user->isAuthenticated()) {
			if ($user->getSignOutReason() === User::INACTIVITY) {
				$this->flashMessage('You have been logged out due to inactivity. Please login again.');
			}
			$backlink = $this->getApplication()->storeRequest();
			$this->redirect('Auth:login', $backlink);
		}

		parent::startup();
	}



	/********************* view default *********************/



	public function renderDefault()
	{
		$this->template->title = "My Albums";

		$album = new Albums;
		$this->template->albums = $album->findAll('artist', 'title');
	}



	/********************* view add *********************/



	public function presentAdd()
	{
		if ($this->request->isMethod('post')) {
			$artist = trim($this->request->post['artist']);
			$title = trim($this->request->post['title']);

			if ($artist != '' && $title != '') {
				$data = array(
					'artist' => $artist,
					'title'  => $title,
				);
				$album = new Albums();
				$album->insert($data);

				$this->flashMessage('The album has been added.');
				$this->redirect('default');
			}
		}

		$this->template->title = "Add New Album";

		// set up an "empty" album
		$album = new Albums();
		$this->template->album = $album->createBlank();

		// additional view fields required by form
		$this->template->action = $this->link('add');
		$this->template->buttonText = 'Add';
	}



	/********************* view edit *********************/



	public function presentEdit($id = 0)
	{
		if ($this->request->isMethod('post')) {
			$artist = trim($this->request->post['artist']);
			$title = trim($this->request->post['title']);

			if ($id !== 0) {
				if ($artist != '' && $title != '') {
					$data = array(
						'artist' => $artist,
						'title'  => $title,
					);
					$album = new Albums();
					$album->update($id, $data);

					$this->flashMessage('The album has been updated.');
					$this->redirect('default');
				}
			}
		}

		$album = new Albums();
		if ($id > 0) {
			$this->template->album = $album->fetch($id);
			if (!$this->template->album) {
				throw new /*Nette\Application\*/BadRequestException('Record not found');
			}

		} else {
			$this->template->album = $album->createBlank();
		}

		$this->template->title = "Edit Album";

		// additional view fields required by form
		$this->template->buttonText = 'Save';
		$this->template->action = $this->link('edit', $id);
	}



	/********************* view delete *********************/



	public function presentDelete($id = 0)
	{
		if ($this->request->isMethod('post')) {
			if (isset($this->request->post['delete']) && $id > 0) {
				$album = new Albums();
				$album->delete($id);
				$this->flashMessage('Album has been deleted.');
			}
			$this->redirect('default');
		}

		if ($id > 0) {
			// only render if we have an id and can find the album.
			$album = new Albums();
			$this->template->album = $album->fetch($id);
			if (!$this->template->album) {
				throw new /*Nette\Application\*/BadRequestException('Record not found');
			}
		}

		$this->template->title = "Delete Album";
		$this->template->action = $this->link('delete', $id);
	}



	/********************* view logout *********************/



	public function presentLogout()
	{
		Environment::getUser()->signOut();
		$this->flashMessage('You have been logged off.');
		$this->redirect('Auth:login');
	}

}
