<?php

/*use Nette\Environment;*/
/*use Nette\Application\AppForm;*/
/*use Nette\Forms\Form;*/
/*use Nette\Web\User;*/

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
		$this->template->albums = $album->findAll()->orderBy('artist')->orderBy('title');
	}



	/********************* views add & edit *********************/



	public function renderAdd()
	{
		$this->template->title = "Add New Album";

		$form = $this->getComponent('albumForm');
		$form['save']->caption = 'Add';
		$this->template->form = $form;
	}



	public function renderEdit($id = 0)
	{
		$this->template->title = "Edit Album";

		$form = $this->getComponent('albumForm');
		$this->template->form = $form;

		if (!$form->isSubmitted()) {
			$album = new Albums;
			$row = $album->find($id)->fetch();
			if (!$row) {
				throw new /*Nette\Application\*/BadRequestException('Record not found');
			}
			$form->setDefaults($row);
		}
	}



	public function albumFormSubmitted(AppForm $form)
	{
		if ($form['save']->isSubmittedBy()) {
			$id = (int) $this->getParam('id');
			$album = new Albums;
			if ($id > 0) {
				$album->update($id, $form->getValues());
				$this->flashMessage('The album has been updated.');
			} else {
				$album->insert($form->getValues());
				$this->flashMessage('The album has been added.');
			}
		}

		$this->redirect('default');
	}



	/********************* view delete *********************/



	public function renderDelete($id = 0)
	{
		$this->template->title = "Delete Album";
		$this->template->form = $this->getComponent('deleteForm');
		$album = new Albums;
		$this->template->album = $album->find($id)->fetch();
		if (!$this->template->album) {
			throw new /*Nette\Application\*/BadRequestException('Record not found');
		}
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



	/********************* action logout *********************/



	public function actionLogout()
	{
		Environment::getUser()->signOut();
		$this->flashMessage('You have been logged off.');
		$this->redirect('Auth:login');
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

			$form->addSubmit('save', 'Save')->getControlPrototype()->class('default');
			$form->addSubmit('cancel', 'Cancel')->setValidationScope(NULL);
			$form->onSubmit[] = array($this, 'albumFormSubmitted');

			$form->addProtection('Please submit this form again (security token has expired).');
			return;

		case 'deleteForm':
			$form = new AppForm($this, $name);
			$form->addSubmit('cancel', 'Cancel');
			$form->addSubmit('delete', 'Delete')->getControlPrototype()->class('default');
			$form->onSubmit[] = array($this, 'deleteFormSubmitted');

			$form->addProtection('Please submit this form again (security token has expired).');
			return;

		default:
			parent::createComponent($name);
		}
	}

}
