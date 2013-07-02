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

	/** @var array */
	private $validationScope;


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
	 * @return SubmitButton  provides a fluent interface
	 */
	public function setValidationScope(/*array*/$scope = NULL)
	{
		$htmlNames = array();
		if ($scope === NULL || $scope === TRUE) {
			$this->validationScope = NULL;
		} else {
			$this->validationScope = array();
			foreach ($scope ?: array() as $control) {
				if (!$control instanceof Nette\Forms\Container && !$control instanceof Nette\Forms\IControl) {
					throw new Nette\InvalidArgumentException('Validation scope accepts only Nette\Forms\Container or Nette\Forms\IControl instances.');
				}
				$this->validationScope[] = $control;
				$htmlNames[] = $control->lookupPath('Nette\Forms\Form');
			}
		}

		$this->control->formnovalidate = $this->validationScope !== NULL;
		$this->control->data['nette-validation-scope'] = $htmlNames ? json_encode($htmlNames) : NULL;
		return $this;
	}


	/**
	 * Gets the validation scope.
	 * @return array|NULL
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
