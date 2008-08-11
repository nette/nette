<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com/
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com/
 * @category   Nette
 * @package    Nette::Forms
 */

/*namespace Nette::Forms;*/



require_once dirname(__FILE__) . '/../ComponentContainer.php';

require_once dirname(__FILE__) . '/../Forms/INamingContainer.php';



/**
 * Container for form controls.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Forms
 * @version    $Revision$ $Date$
 */
class FormContainer extends /*Nette::*/ComponentContainer implements /*::*/ArrayAccess, INamingContainer
{


	/********************* control factories ****************d*g**/



	/**
	 * Adds single-line text input control to the form.
	 * @param  string  control name
	 * @param  string  label
	 * @param  int  width of the control
	 * @param  int  maximum number of characters the user may enter
	 * @return TextInput
	 */
	public function addText($name, $label, $cols = NULL, $maxLength = NULL)
	{
		$control = new TextInput($label, $cols, $maxLength);
		$control->getControlPrototype()->class[] = 'text';
		$this->addComponent($control, $name);
		return $control;
	}



	/**
	 * Adds single-line text input control used for sensitive input such as passwords.
	 * @param  string  control name
	 * @param  string  label
	 * @param  int  width of the control
	 * @param  int  maximum number of characters the user may enter
	 * @return TextInput
	 */
	public function addPassword($name, $label, $cols = NULL, $maxLength = NULL)
	{
		$control = new TextInput($label, $cols, $maxLength);
		$control->getControlPrototype()->class[] = 'text';
		$control->setPasswordMode(TRUE);
		$this->addComponent($control, $name);
		return $control;
	}



	/**
	 * Adds multi-line text input control to the form.
	 * @param  string  control name
	 * @param  string  label
	 * @param  int  width of the control
	 * @param  int  height of the control in text lines
	 * @return TextArea
	 */
	public function addTextArea($name, $label, $cols = 40, $rows = 10)
	{
		$control = new TextArea($label, $cols, $rows);
		$this->addComponent($control, $name);
		return $control;
	}



	/**
	 * Adds control that allows the user to upload files.
	 * @param  string  control name
	 * @param  string  label
	 * @return FileUpload
	 */
	public function addFile($name, $label)
	{
		$control = new FileUpload($label);
		$control->getControlPrototype()->class[] = 'text';
		$this->addComponent($control, $name);
		return $control;
	}



	/**
	 * Adds hidden form control used to store a non-displayed value.
	 * @param  string  control name
	 * @return HiddenField
	 */
	public function addHidden($name)
	{
		return $this[$name] = new HiddenField;
	}



	/**
	 * Adds check box control to the form.
	 * @param  string  control name
	 * @param  string  label
	 * @return Checkbox
	 */
	public function addCheckbox($name, $label)
	{
		return $this[$name] = new Checkbox($label);
	}



	/**
	 * Adds set of radio button controls to the form.
	 * @param  string  control name
	 * @param  string  label
	 * @param  array   options from which to choose
	 * @return RadioList
	 */
	public function addRadioList($name, $label, array $items)
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
	public function addSelect($name, $label, array $items, $size = 1)
	{
		return $this[$name] = new SelectBox($label, $items, FALSE, $size);
	}



	/**
	 * Adds select box control that allows multiple item selection.
	 * @param  string  control name
	 * @param  string  label
	 * @param  array   options from which to choose
	 * @param  int     number of rows that should be visible
	 * @return SelectBox
	 */
	public function addMultiSelect($name, $label, array $items, $size = 1)
	{
		return $this[$name] = new SelectBox($label, $items, TRUE, $size);
	}



	/**
	 * Adds button used to submit form.
	 * @param  string  control name
	 * @param  string  label
	 * @return SubmitButton
	 */
	public function addSubmit($name, $label)
	{
		$control = new SubmitButton($label);
		$control->getControlPrototype()->class[] = 'button';
		$this->addComponent($control, $name);
		return $control;
	}



	/**
	 * Adds push buttons with no default behavior.
	 * @param  string  control name
	 * @param  string  label
	 * @return Button
	 */
	public function addButton($name, $label)
	{
		$control = new Button($label);
		$control->getControlPrototype()->class[] = 'button';
		$this->addComponent($control, $name);
		return $control;
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
		return $this[$name] = new FormContainer;
	}



	/**
	 * Adds control that repeats a specified prototype for each item in the list.
	 * @param  string  control name
	 * @return RepeaterControl
	 */
	public function addRepeater($name)
	{
		return $this[$name] = new RepeaterControl;
	}



	/********************* interface ::ArrayAccess ****************d*g**/



	/**
	 * Adds the component to the container.
	 * @param  string  component name
	 * @param  Nette::IComponent
	 * @return void.
	 */
	final public function offsetSet($name, $component)
	{
		$this->addComponent($component, $name);
	}



	/**
	 * Returns component specified by name. Throws exception if component doesn't exist.
	 * @param  string  component name
	 * @return Nette::IComponent
	 * @throws ::InvalidArgumentException
	 */
	final public function offsetGet($name)
	{
		return $this->getComponent($name, TRUE);
	}



	/**
	 * Does component specified by name exists?
	 * @param  string  component name
	 * @return boolean
	 */
	final public function offsetExists($name)
	{
		return $this->getComponent($name) !== NULL;
	}



	/**
	 * Removes component from the container. Throws exception if component doesn't exist.
	 * @param  string  component name
	 * @return void
	 */
	final public function offsetUnset($name)
	{
		$this->removeComponent($this->getComponent($name, TRUE));
	}

}
