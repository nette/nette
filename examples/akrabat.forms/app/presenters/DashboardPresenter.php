<?php

/*use Nette\Environment;*/

require_once dirname(__FILE__) . '/BasePresenter.php';


class DashboardPresenter extends BasePresenter
{


	protected function startup()
	{
		require_once 'models/Albums.php';

		// user authentication
		$user = Environment::getUser();
		if (!$user->isAuthenticated()) {
			$backlink = $this->getApplication()->storeRequest();
			$this->redirect('Auth:login', $backlink);
		}

		parent::startup();
	}



	/********************* view default *********************/



	public function renderDefault()
	{
		$album = new Albums();
		$this->template->albums = $album->findAll('artist', 'title');
		$this->template->title = "My Albums";
	}



	/********************* view add *********************/



	public function renderAdd()
	{
		$this->renderEdit();
	}



	/********************* view edit *********************/



	public function renderEdit($id = 0)
	{
		$this->template->form = $this->getComponent('editForm');
		$this->template->title = $id > 0 ? "Edit Album" : "Add New Album";
	}



	public function editFormSubmitted(AppForm $form)
	{
		$id = (int) $this->getParam('id');
		$album = new Albums();
		if ($id > 0) {
			$album->update($id, $form->getValues());
		} else {
			$album->insert($form->getValues());
		}
		$this->redirect('default');
	}



	/********************* view delete *********************/



	public function renderDelete($id = 0)
	{
		$this->template->form = $this->getComponent('deleteForm');
		$album = new Albums();
		$this->template->album = $album->fetch($id);
		$this->template->title = "Delete Album";

	}



	public function deleteFormSubmitted(AppForm $form)
	{
		if ($form['yes']->isSubmittedBy()) {
			$album = new Albums();
			$album->delete((int) $this->getParam('id'));
		}

		$this->redirect('default');
	}



	/********************* view logout *********************/



	public function presentLogout()
	{
		Environment::getUser()->signOut();
		$this->redirect('default');
	}



	/********************* facilities *********************/



	/**
	 * Component factory.
	 * @param  string  component name
	 * @return void
	 */
	protected function createComponent($name)
	{
		switch ($name) {
		case 'editForm':
			$id = $this->getParam('id');
			$form = new AppForm($this, $name);
			$form->addText('artist', 'Artist:')
				->addRule(Form::FILLED, 'Please enter an artist.');

			$form->addText('title', 'Title:')
				->addRule(Form::FILLED, 'Please enter a title.');

			$form->addSubmit('submit1', $id > 0 ? 'Edit' : 'Add');
			$form->onSubmit[] = array($this, 'editFormSubmitted');

			if (!$form->isSubmitted()) {
				$album = new Albums();
				if ($id > 0) {
					$form->setDefaults($album->fetch($id));
				} else {
					$form->setDefaults($album->createBlank());
				}
			}
			return;

		case 'deleteForm':
			$form = new AppForm($this, $name);
			$form->addSubmit('yes', 'Yes');
			$form->addSubmit('no', 'No');
			$form->onSubmit[] = array($this, 'deleteFormSubmitted');
			return;

		default:
			parent::createComponent($name);
		}
	}

}
