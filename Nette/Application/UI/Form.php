<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application\UI;

use Nette;


/**
 * Web form adapted for Presenter.
 *
 * @author     David Grudl
 *
 * @property-read Presenter $presenter
 */
class Form extends Nette\Forms\Form implements ISignalReceiver
{

	/**
	 * Application form constructor.
	 */
	public function __construct(Nette\ComponentModel\IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct();
		if ($parent !== NULL) {
			$parent->addComponent($this, $name);
		}
	}


	/**
	 * @return void
	 */
	protected function validateParent(Nette\ComponentModel\IContainer $parent)
	{
		parent::validateParent($parent);
		$this->monitor('Nette\Application\UI\Presenter');
	}


	/**
	 * Returns the presenter where this component belongs to.
	 * @param  bool   throw exception if presenter doesn't exist?
	 * @return Presenter|NULL
	 */
	public function getPresenter($need = TRUE)
	{
		return $this->lookup('Nette\Application\UI\Presenter', $need);
	}


	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 * @param  Nette\ComponentModel\IComponent
	 * @return void
	 */
	protected function attached($presenter)
	{
		if ($presenter instanceof Presenter) {
			$name = $this->lookupPath('Nette\Application\UI\Presenter');

			if (!isset($this->getElementPrototype()->id)) {
				$this->getElementPrototype()->id = 'frm-' . $name;
			}

			if (!$this->getAction()) {
				$this->setAction(new Link($presenter, 'this', array()));
			}

			if (iterator_count($this->getControls()) && $this->isSubmitted()) {
				foreach ($this->getControls() as $control) {
					if (!$control->isDisabled()) {
						$control->loadHttpData();
					}
				}
			}

			$signal = new Nette\Forms\Controls\HiddenField($name . self::NAME_SEPARATOR . 'submit');
			$signal->setOmitted()->setHtmlId(FALSE);
			$this[Presenter::SIGNAL_KEY] = $signal;
		}
		parent::attached($presenter);
	}


	/**
	 * Tells if the form is anchored.
	 * @return bool
	 */
	public function isAnchored()
	{
		return (bool) $this->getPresenter(FALSE);
	}


	/**
	 * Internal: returns submitted HTTP data or NULL when form was not submitted.
	 * @return array|NULL
	 */
	protected function receiveHttpData()
	{
		$presenter = $this->getPresenter();
		if (!$presenter->isSignalReceiver($this, 'submit')) {
			return;
		}

		$isPost = $this->getMethod() === self::POST;
		$request = $presenter->getRequest();
		if ($request->isMethod('forward') || $request->isMethod('post') !== $isPost) {
			return;
		}

		if ($isPost) {
			return Nette\Utils\Arrays::mergeTree($request->getPost(), $request->getFiles());
		} else {
			return $request->getParameters();
		}
	}


	/********************* interface ISignalReceiver ****************d*g**/


	/**
	 * This method is called by presenter.
	 * @param  string
	 * @return void
	 */
	public function signalReceived($signal)
	{
		if ($signal === 'submit') {
			if (!$this->getPresenter()->getRequest()->hasFlag(Nette\Application\Request::RESTORED)) {
				$this->fireEvents();
			}
		} else {
			$class = get_class($this);
			throw new BadSignalException("Missing handler for signal '$signal' in $class.");
		}
	}

}
