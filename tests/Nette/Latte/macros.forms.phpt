<?php

/**
 * Test: Nette\Latte\Engine and FormMacros.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Nette\Templating\FileTemplate,
	Nette\Forms\Form,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';


class MyControl extends Nette\Forms\Controls\BaseControl
{
	function getLabel($c = NULL)
	{
		return '<label>My</label>';
	}

	function getControl()
	{
		return '<input name=My>';
	}
}


$form = new Form;
$form->addHidden('id');
$form->addText('username', 'Username:'); // must have just one textfield to generate IE fix
$form->addRadioList('sex', 'Sex:', array('m' => 'male', 'f' => 'female'));
$form->addSelect('select', NULL, array('m' => 'male', 'f' => 'female'));
$form->addTextArea('area', NULL)->setValue('one<two');
$form->addCheckbox('checkbox', NULL);
$form->addCheckboxList('checklist', NULL, array('m' => 'male', 'f' => 'female'));
$form->addSubmit('send', 'Sign in');
$form['my'] = new MyControl;

$template = new FileTemplate(__DIR__ . '/templates/forms.latte');
$template->registerFilter(new Latte\Engine);
$template->_control = array('myForm' => $form);

$form['username']->addError('error');

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile("$path.phtml", codefix($template->compile()));
Assert::matchFile("$path.html", $template->__toString(TRUE));
