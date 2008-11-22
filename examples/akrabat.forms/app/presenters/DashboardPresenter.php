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



	/********************* views add & edit *********************/



	public function renderAdd()
	{
		$this->template->title = "Add New Album";

		$form = $this->getComponent('albumForm');
		$form['submit1']->caption = 'Add';
		$this->template->form = $form;

		if (!$form->isSubmitted()) {
			$album = new Albums;
			$form->setDefaults($album->createBlank());
		}
	}



	public function renderEdit($id = 0)
	{
		$this->template->title = "Edit Album";

		$form = $this->getComponent('albumForm');
		$this->template->form = $form;

		if (!$form->isSubmitted()) {
			$album = new Albums;
			$form->setDefaults($album->fetch($id));
		}
	}



	public function albumFormSubmitted(AppForm $form)
	{
		$id = (int) $this->getParam('id');
		$album = new Albums;
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
		$this->template->title = "Delete Album";
		$this->template->form = $this->getComponent('deleteForm');
		$album = new Albums;
		$this->template->album = $album->fetch($id);

	}



	public function deleteFormSubmitted(AppForm $form)
	{
		if ($form['yes']->isSubmittedBy()) {
			$album = new Albums;
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
		case 'albumForm':
			$id = $this->getParam('id');
			$form = new AppForm($this, $name);
			$form->addText('artist', 'Artist:')
				->addRule(Form::FILLED, 'Please enter an artist.');

			$form->addText('title', 'Title:')
				->addRule(Form::FILLED, 'Please enter a title.');

			$form->addSubmit('submit1', 'Edit');
			$form->onSubmit[] = array($this, 'albumFormSubmitted');
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
