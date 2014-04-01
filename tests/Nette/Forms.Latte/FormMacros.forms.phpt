<?php

/**
 * Test: FormMacros.
 *
 * @author     David Grudl
 */

use Nette\Forms\Form,
	Nette\Bridges\FormsLatte\FormMacros,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


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

$latte = new Latte\Engine;
FormMacros::install($latte->getCompiler());

$form['username']->addError('error');

Assert::matchFile(
	__DIR__ . '/expected/FormMacros.forms.phtml',
	$latte->compile(__DIR__ . '/templates/forms.latte')
);
Assert::matchFile(
	__DIR__ . '/expected/FormMacros.forms.html',
	$latte->renderToString(
		__DIR__ . '/templates/forms.latte',
		array('_control' => array('myForm' => $form))
	)
);
