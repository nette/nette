<?php

use Nette\Application\AppForm,
	Nette\Forms\Form;



class DashboardPresenter extends BasePresenter
{

	protected function startup()
	{
		// user authentication
		if (!$this->user->isLoggedIn()) {
			if ($this->user->logoutReason === Nette\Web\User::INACTIVITY) {
				$this->flashMessage('You have been logged out due to inactivity. Please login again.');
			}
			$backlink = $this->application->storeRequest();
			$this->redirect('Auth:login', array('backlink' => $backlink));
		}

		parent::startup();
	}



	/********************* view default *********************/



	public function renderDefault()
	{
		$album = new Albums;
		$this->template->albums = $album->findAll()->orderBy('artist')->orderBy('title');
	}



	/********************* views add & edit *********************/



	public function renderAdd()
	{
		$this['albumForm']['save']->caption = 'Add';
	}



	public function renderEdit($id = 0)
	{
		$form = $this['albumForm'];
		if (!$form->isSubmitted()) {
			$album = new Albums;
			$row = $album->find($id)->fetch();
			if (!$row) {
				throw new Nette\Application\BadRequestException('Record not found');
			}
			$form->setDefaults($row);
		}
	}



	/********************* view delete *********************/



	public function renderDelete($id = 0)
	{
		$album = new Albums;
		$this->template->album = $album->find($id)->fetch();
		if (!$this->template->album) {
			throw new Nette\Application\BadRequestException('Record not found');
		}
	}



	/********************* action logout *********************/



	public function actionLogout()
	{
		$this->user->logout();
		$this->flashMessage('You have been logged off.');
		$this->redirect('Auth:login');
	}



	/********************* component factories *********************/



	/**
	 * Album edit form component factory.
	 * @return mixed
	 */
	protected function createComponentAlbumForm()
	{
		$form = new AppForm;
		$form->addText('artist', 'Artist:')
			->addRule(Form::FILLED, 'Please enter an artist.');

		$form->addText('title', 'Title:')
			->addRule(Form::FILLED, 'Please enter a title.');

		$form->addSubmit('save', 'Save')->setAttribute('class', 'default');
		$form->addSubmit('cancel', 'Cancel')->setValidationScope(NULL);
		$form->onSubmit[] = callback($this, 'albumFormSubmitted');

		$form->addProtection('Please submit this form again (security token has expired).');
		return $form;
	}



	public function albumFormSubmitted(AppForm $form)
	{
		if ($form['save']->isSubmittedBy()) {
			$id = (int) $this->getParam('id');
			$album = new Albums;
			if ($id > 0) {
				$album->update($id, $form->values);
				$this->flashMessage('The album has been updated.');
			} else {
				$album->insert($form->values);
				$this->flashMessage('The album has been added.');
			}
		}

		$this->redirect('default');
	}



	/**
	 * Album delete form component factory.
	 * @return mixed
	 */
	protected function createComponentDeleteForm()
	{
		$form = new AppForm;
		$form->addSubmit('cancel', 'Cancel');
		$form->addSubmit('delete', 'Delete')->setAttribute('class', 'default');
		$form->onSubmit[] = callback($this, 'deleteFormSubmitted');
		$form->addProtection('Please submit this form again (security token has expired).');
		return $form;
	}



	public function deleteFormSubmitted(AppForm $form)
	{
		if ($form['delete']->isSubmittedBy()) {
			$album = new Albums;
			$album->delete($this->getParam('id'));
			$this->flashMessage('Album has been deleted.');
		}

		$this->redirect('default');
	}

}
