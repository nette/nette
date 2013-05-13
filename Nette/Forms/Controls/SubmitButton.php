<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Forms\Controls;

use Nette;



/**
 * Submittable button control.
 *
 * @author     David Grudl
 *
 * @property-read bool $submittedBy
 * @property   mixed $validationScope
 */
class SubmitButton extends Button implements Nette\Forms\ISubmitterControl
{
	/** @var array of function(SubmitButton $sender); Occurs when the button is clicked and form is successfully validated */
	public $onClick;

	/** @var array of function(SubmitButton $sender); Occurs when the button is clicked and form is not validated */
	public $onInvalidClick;

	/** @var mixed */
	private $validationScope = TRUE;



	/**
	 * @param  string  caption
	 */
	public function __construct($caption = NULL)
	{
		parent::__construct($caption);
		$this->control->type = 'submit';
		$this->setOmitted(TRUE);
	}



	/**
	 * Sets 'pressed' indicator.
	 * @param  bool
	 * @return SubmitButton  provides a fluent interface
	 */
	public function setValue($value)
	{
		if ($this->value = $value !== NULL) {
			$this->getForm()->setSubmittedBy($this);
		}
		return $this;
	}



	/**
	 * Tells if the form was submitted by this button.
	 * @return bool
	 */
	public function isSubmittedBy()
	{
		return $this->getForm()->isSubmitted() === $this;
	}



	/**
	 * Sets the validation scope. Clicking the button validates only the controls within the specified scope.
	 * @param  mixed
	 * @return SubmitButton  provides a fluent interface
	 */
	public function setValidationScope($scope)
	{
		if (is_bool($scope) || empty($scope)) {
			$this->validationScope = (bool) $scope;
			$this->control->formnovalidate = !$this->validationScope;

		} else {
			$this->validationScope = array();
			$htmlNames = array();
			foreach ((array) $scope as $component) {
				if (!is_object($component)) {
					$component = $this->form->getComponent($component);
				}
				if (!$component instanceof Nette\Forms\Container && !$component instanceof Nette\Forms\IControl) {
					throw new Nette\InvalidArgumentException('Validation scope accepts only Nette\Forms\Container or Nette\Forms\IControl instances.');
				}
				$this->validationScope[] = $component;
				$htmlNames[] = $component->lookupPath('Nette\Forms\Form');
			}

			$this->control->formnovalidate = TRUE;
			$this->control->data['nette-validation-scope'] = json_encode($htmlNames);
		}

		return $this;
	}



	/**
	 * Gets the validation scope.
	 * @return mixed
	 */
	final public function getValidationScope()
	{
		return $this->validationScope;
	}



	/**
	 * Fires click event.
	 * @return void
	 */
	public function click()
	{
		$this->onClick($this);
	}



	/**
	 * Submitted validator: has been button pressed?
	 * @return bool
	 */
	public static function validateSubmitted(SubmitButton $control)
	{
		return $control->isSubmittedBy();
	}

}
