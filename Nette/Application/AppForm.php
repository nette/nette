<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Application
 */

/*namespace Nette\Application;*/



require_once dirname(__FILE__) . '/../Forms/Form.php';

require_once dirname(__FILE__) . '/../Application/ISignalReceiver.php';



/**
 * Web form as presenter component.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Application
 *
 * @property-read Presenter $presenter
 */
class AppForm extends /*Nette\Forms\*/Form implements ISignalReceiver
{

	/**
	 * Application form constructor.
	 */
	public function __construct(/*Nette\*/IComponentContainer $parent = NULL, $name = NULL)
	{
		parent::__construct();
		$this->monitor('Nette\Application\Presenter');
		if ($parent !== NULL) {
			$parent->addComponent($this, $name);
		}	
	}



	/**
	 * Returns the presenter where this component belongs to.
	 * @param  bool   throw exception if presenter doesn't exist?
	 * @return Presenter|NULL
	 */
	public function getPresenter($need = TRUE)
	{
		return $this->lookup('Nette\Application\Presenter', $need);
	}



	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 * @param  IComponent
	 * @return void
	 */
	protected function attached($presenter)
	{
		if ($presenter instanceof Presenter) {
			$this->setAction(new Link(
				$presenter,
				$this->lookupPath('Nette\Application\Presenter') . self::NAME_SEPARATOR . 'submit!',
				array()
			));

			// fill-in the form with HTTP data
			if ($this->isSubmitted()) {
				foreach ($this->getControls() as $control) {
					$control->loadHttpData();
				}
			}
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
	 * Tells if the form was submitted.
	 * @return ISubmitterControl|FALSE  submittor control
	 */
	public function isSubmitted()
	{
		if ($this->submittedBy === NULL) {
			$this->submittedBy = FALSE;
			$presenter = $this->getPresenter();
			if ($presenter->isSignalReceiver($this, 'submit')) {
				$isPost = strcasecmp($this->getMethod(), 'post') === 0;
				$request = $presenter->getRequest();
				if (!$request->isMethod('forward') && $request->isMethod('post') === $isPost) {
					$this->submittedBy = TRUE;
				}
			}
		}
		return $this->submittedBy;
	}



	/**
	 * Returns submitted HTTP data.
	 * @return array
	 */
	public function getHttpData()
	{
		if ($this->httpData === NULL && $this->isSubmitted()) {
			$request = $this->getPresenter()->getRequest();
			if (strcasecmp($this->getMethod(), 'post') === 0) {
				$this->httpData = /*Nette\*/ArrayTools::mergeTree($request->getPost(), $request->getFiles());
			} else {
				$this->httpData = $request->getParams();
			}
		}
		return $this->httpData;
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
			$this->submit();

		} else {
			throw new BadSignalException("There is no handler for signal '$signal' in '{$this->getClass()}'.");
		}
	}

}
