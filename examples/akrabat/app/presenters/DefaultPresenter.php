<?php

require_once dirname(__FILE__) . '/BasePresenter.php';


class DefaultPresenter extends BasePresenter
{

	protected function startup()
	{
		require_once 'models/Albums.php';

		// user authentication
		$user = Environment::getUser();
		if (!$user->isAuthenticated()) {
			$this->redirect('auth:login', $this->backlink());
		}

		$this->template->user = $user->getIdentity();
		parent::startup();
	}



	/********************* view Default *********************/



	public function renderDefault()
	{
		$this->template->title = "My Albums";
		$album = new Albums();
		$this->template->albums = $album->findAll('artist', 'title');
	}



	/********************* view Add *********************/



	public function renderAdd()
	{
		$this->template->title = "Add New Album";

		// set up an "empty" album
		$album = new Albums();
		$this->template->album = $album->createBlank();

		// additional view fields required by form
		$this->template->action = $this->link('add!');
		$this->template->buttonText = 'Add';
	}



	public function handleAdd()
	{
		$request = $this->request;
		if (!$request->isPost()) return;

		$artist = trim($request->post['artist']);
		$title = trim($request->post['title']);

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



	/********************* view Edit *********************/



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
		$this->template->action = $this->link('save!', $id);
		$this->template->buttonText = 'Update';
	}



	public function handleSave($id = 0)
	{
		$request = $this->request;
		if (!$request->isPost()) return;

		$artist = trim($request->post['artist']);
		$title = trim($request->post['title']);

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



	/********************* view Delete *********************/



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

		$this->template->action = $this->link('delete!', $id);
	}



	public function handleDelete($id = 0)
	{
		$request = $this->request;
		if (!$request->isPost()) return;

		$del = $request->post['del'];
		if ($del == 'Yes' && $id > 0) {
			$album = new Albums();
			$album->delete($id);
		}
		$this->redirect('default');
	}



	/********************* common commands *********************/



	function handleLogout()
	{
		Environment::getUser()->signOut();
		$this->redirect('default');
	}

}
