<?php

/*use Nette::Environment;*/

require_once dirname(__FILE__) . '/BasePresenter.php';


class DashboardPresenter extends BasePresenter
{

	protected function startup()
	{
		require_once 'models/Albums.php';

		// user authentication
		$user = Environment::getUser();
		if (!$user->isAuthenticated()) {
			$backlink = $user->storeRequest($this->getRequest());
			$this->redirect('Auth:login', $backlink);
		}

		parent::startup();
	}



	/********************* view default *********************/



	public function renderDefault()
	{
		$this->template->title = "My Albums";

		$album = new Albums();
		$this->template->albums = $album->findAll('artist', 'title');
	}



	/********************* view add *********************/



	public function presentAdd()
	{
		if (!$this->request->isPost()) return; // continue to renderAdd()...

		$artist = trim($this->request->post['artist']);
		$title = trim($this->request->post['title']);

		if ($artist != '' && $title != '') {
			$data = array(
				'artist' => $artist,
				'title'  => $title,
			);
			$album = new Albums();
			$album->insert($data);

			$this->redirect('default');
		}
	}



	public function renderAdd()
	{
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
		if (!$this->request->isPost()) return; // continue to renderEdit()...

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

				$this->redirect('default');

			} else {
				//$this->template->album = $album->fetch($id);
			}
		}
	}



	public function renderEdit($id = 0)
	{
		$this->template->title = "Edit Album";

		$album = new Albums();
		if ($id > 0) {
			$this->template->album = $album->fetch($id);
		} else {
			$this->template->album = $album->createBlank();
		}

		// additional view fields required by form
		$this->template->action = $this->link('edit', $id);
		$this->template->buttonText = 'Update';
	}



	/********************* view delete *********************/



	public function presentDelete($id = 0)
	{
		if (!$this->request->isPost()) return; // continue to renderDelete()...

		$del = $this->request->post['del'];
		if ($del == 'Yes' && $id > 0) {
			$album = new Albums();
			$album->delete($id);
		}

		$this->redirect('default');
	}



	public function renderDelete($id = 0)
	{
		$this->template->title = "Delete Album";

		if ($id > 0) {
			// only render if we have an id and can find the album.
			$album = new Albums();
			$this->template->album = $album->fetch($id);
			if (!$this->template->album) {
				$this->redirect('default');
			}
		}

		$this->template->action = $this->link('delete', $id);
	}



	/********************* view logout *********************/



	public function presentLogout()
	{
		Environment::getUser()->signOut();
		$this->redirect('default');
	}

}
