<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Forms;

use Nette;



/**
 * Container for form controls.
 *
 * @author     David Grudl
 *
 * @property-read \ArrayIterator $controls
 * @property-read Form $form
 * @property-read bool $valid
 * @property   array $values
 */
class FormContainer extends Nette\ComponentContainer implements \ArrayAccess
{
	/** @var array of function(Form $sender); Occurs when the form is validated */
	public $onValidate;

	/** @var FormGroup */
	protected $currentGroup;

	/** @var bool */
	protected $valid;



	/********************* data exchange ****************d*g**/



	/**
	 * Fill-in with default values.
	 * @param  array|Traversable  values used to fill the form
	 * @param  bool     erase other default values?
	 * @return FormContainer  provides a fluent interface
	 */
	public function setDefaults($values, $erase = FALSE)
	{
		$form = $this->getForm(FALSE);
		if (!$form || !$form->isAnchored() || !$form->isSubmitted()) {
			$this->setValues($values, $erase);
		}
		return $this;
	}



	/**
	 * Fill-in with values.
	 * @param  array|Traversable  values used to fill the form
	 * @param  bool     erase other controls?
	 * @return FormContainer  provides a fluent interface
	 */
	public function setValues($values, $erase = FALSE)
	{
		if ($values instanceof \Traversable) {
			$values = iterator_to_array($values);

		} elseif (!is_array($values)) {
			throw new \InvalidArgumentException("Values must be an array, " . gettype($values) ." given.");
		}

		$cursor = & $values;
		$iterator = $this->getComponents(TRUE);
		foreach ($iterator as $name => $control) {
			$sub = $iterator->getSubIterator();
			if (!isset($sub->cursor)) {
				$sub->cursor = & $cursor;
			}
			if ($control instanceof IFormControl) {
				if ((is_array($sub->cursor) || $sub->cursor instanceof \ArrayAccess) && array_key_exists($name, $sub->cursor)) {
					$control->setValue($sub->cursor[$name]);

				} elseif ($erase) {
					$control->setValue(NULL);
				}
			}
			if ($control instanceof FormContainer) {
				if ((is_array($sub->cursor) || $sub->cursor instanceof \ArrayAccess) && isset($sub->cursor[$name])) {
					$cursor = & $sub->cursor[$name];
				} else {
					unset($cursor);
					$cursor = NULL;
				}
			}
		}
		return $this;
	}



	/**
	 * Returns the values submitted by the form.
	 * @return array
	 */
	public function getValues()
	{
		$values = array();
		$cursor = & $values;
		$iterator = $this->getComponents(TRUE);
		foreach ($iterator as $name => $control) {
			$sub = $iterator->getSubIterator();
			if (!isset($sub->cursor)) {
				$sub->cursor = & $cursor;
			}
			if ($control instanceof IFormControl && !$control->isDisabled() && !($control instanceof ISubmitterControl)) {
				$sub->cursor[$name] = $control->getValue();
			}
			if ($control instanceof FormContainer) {
				$cursor = & $sub->cursor[$name];
				$cursor = array();
			}
		}
		return $values;
	}



	/********************* validation ****************d*g**/



	/**
	 * Is form valid?
	 * @return bool
	 */
	public function isValid()
	{
		if ($this->valid === NULL) {
			$this->validate();
		}
		return $this->valid;
	}



	/**
	 * Performs the server side validation.
	 * @return void
	 */
	public function validate()
	{
		$this->valid = TRUE;
		$this->onValidate($this);
		foreach ($this->getControls() as $control) {
			if (!$control->getRules()->validate()) {
				$this->valid = FALSE;
			}
		}
	}



	/********************* form building ****************d*g**/



	/**
	 * @param  FormGroup
	 * @return FormContainer  provides a fluent interface
	 */
	public function setCurrentGroup(FormGroup $group = NULL)
	{
		$this->currentGroup = $group;
		return $this;
	}



	/**
	 * Returns current group.
	 * @return FormGroup
	 */
	public function getCurrentGroup()
	{
		return $this->currentGroup;
	}



	/**
	 * Adds the specified component to the IComponentContainer.
	 * @param  IComponent
	 * @param  string
	 * @param  string
	 * @return void
	 * @throws \InvalidStateException
	 */
	public function addComponent(Nette\IComponent $component, $name, $insertBefore = NULL)
	{
		parent::addComponent($component, $name, $insertBefore);
		if ($this->currentGroup !== NULL && $component instanceof IFormControl) {
			$this->currentGroup->add($component);
		}
	}



	/**
	 * Iterates over all form controls.
	 * @return \ArrayIterator
	 */
	public function getControls()
	{
		return $this->getComponents(TRUE, 'Nette\Forms\IFormControl');
	}



	/**
	 * Returns form.
	 * @param  bool   throw exception if form doesn't exist?
	 * @return Form
	 */
	public function getForm($need = TRUE)
	{
		return $this->lookup('Nette\Forms\Form', $need);
	}



	/********************* control factories ****************d*g**/



	/**
	 * Adds single-line text input control to the form.
	 * @param  string  control name
	 * @param  string  label
	 * @param  int  width of the control
	 * @param  int  maximum number of characters the user may enter
	 * @return TextInput
	 */
	public function addText($name, $label = NULL, $cols = NULL, $maxLength = NULL)
	{
		return $this[$name] = new TextInput($label, $cols, $maxLength);
	}



	/**
	 * Adds single-line text input control used for sensitive input such as passwords.
	 * @param  string  control name
	 * @param  string  label
	 * @param  int  width of the control
	 * @param  int  maximum number of characters the user may enter
	 * @return TextInput
	 */
	public function addPassword($name, $label = NULL, $cols = NULL, $maxLength = NULL)
	{
		$control = new TextInput($label, $cols, $maxLength);
		$control->setType('password');
		return $this[$name] = $control;
	}



	/**
	 * Adds multi-line text input control to the form.
	 * @param  string  control name
	 * @param  string  label
	 * @param  int  width of the control
	 * @param  int  height of the control in text lines
	 * @return TextArea
	 */
	public function addTextArea($name, $label = NULL, $cols = 40, $rows = 10)
	{
		return $this[$name] = new TextArea($label, $cols, $rows);
	}



	/**
	 * Adds control that allows the user to upload files.
	 * @param  string  control name
	 * @param  string  label
	 * @return FileUpload
	 */
	public function addFile($name, $label = NULL)
	{
		return $this[$name] = new FileUpload($label);
	}



	/**
	 * Adds hidden form control used to store a non-displayed value.
	 * @param  string  control name
	 * @param  mixed   default value
	 * @return HiddenField
	 */
	public function addHidden($name, $default = NULL)
	{
		$control = new HiddenField;
		$control->setDefaultValue($default);
		return $this[$name] = $control;
	}



	/**
	 * Adds check box control to the form.
	 * @param  string  control name
	 * @param  string  caption
	 * @return Checkbox
	 */
	public function addCheckbox($name, $caption = NULL)
	{
		return $this[$name] = new Checkbox($caption);
	}



	/**
	 * Adds set of radio button controls to the form.
	 * @param  string  control name
	 * @param  string  label
	 * @param  array   options from which to choose
	 * @return RadioList
	 */
	public function addRadioList($name, $label = NULL, array $items = NULL)
	{
		return $this[$name] = new RadioList($label, $items);
	}



	/**
	 * Adds select box control that allows single item selection.
	 * @param  string  control name
	 * @param  string  label
	 * @param  array   items from which to choose
	 * @param  int     number of rows that should be visible
	 * @return SelectBox
	 */
	public function addSelect($name, $label = NULL, array $items = NULL, $size = NULL)
	{
		return $this[$name] = new SelectBox($label, $items, $size);
	}



	/**
	 * Adds select box control that allows multiple item selection.
	 * @param  string  control name
	 * @param  string  label
	 * @param  array   options from which to choose
	 * @param  int     number of rows that should be visible
	 * @return MultiSelectBox
	 */
	public function addMultiSelect($name, $label = NULL, array $items = NULL, $size = NULL)
	{
		return $this[$name] = new MultiSelectBox($label, $items, $size);
	}



	/**
	 * Adds button used to submit form.
	 * @param  string  control name
	 * @param  string  caption
	 * @return SubmitButton
	 */
	public function addSubmit($name, $caption = NULL)
	{
		return $this[$name] = new SubmitButton($caption);
	}



	/**
	 * Adds push buttons with no default behavior.
	 * @param  string  control name
	 * @param  string  caption
	 * @return Button
	 */
	public function addButton($name, $caption)
	{
		return $this[$name] = new Button($caption);
	}



	/**
	 * Adds graphical button used to submit form.
	 * @param  string  control name
	 * @param  string  URI of the image
	 * @param  string  alternate text for the image
	 * @return ImageButton
	 */
	public function addImage($name, $src = NULL, $alt = NULL)
	{
		return $this[$name] = new ImageButton($src, $alt);
	}



	/**
	 * Adds naming container to the form.
	 * @param  string  name
	 * @return FormContainer
	 */
	public function addContainer($name)
	{
		$control = new FormContainer;
		$control->currentGroup = $this->currentGroup;
		return $this[$name] = $control;
	}



	/********************* interface \ArrayAccess ****************d*g**/



	/**
	 * Adds the component to the container.
	 * @param  string  component name
	 * @param  Nette\IComponent
	 * @return void
	 */
	final public function offsetSet($name, $component)
	{
		$this->addComponent($component, $name);
	}



	/**
	 * Returns component specified by name. Throws exception if component doesn't exist.
	 * @param  string  component name
	 * @return Nette\IComponent
	 * @throws \InvalidArgumentException
	 */
	final public function offsetGet($name)
	{
		return $this->getComponent($name, TRUE);
	}



	/**
	 * Does component specified by name exists?
	 * @param  string  component name
	 * @return bool
	 */
	final public function offsetExists($name)
	{
		return $this->getComponent($name, FALSE) !== NULL;
	}



	/**
	 * Removes component from the container.
	 * @param  string  component name
	 * @return void
	 */
	final public function offsetUnset($name)
	{
		$component = $this->getComponent($name, FALSE);
		if ($component !== NULL) {
			$this->removeComponent($component);
		}
	}



	/**
	 * Prevents cloning.
	 */
	final public function __clone()
	{
		throw new \NotImplementedException('Form cloning is not supported yet.');
	}

}
