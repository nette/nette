<?php

/**
 * Test: FormMacros: {formContainer}
 *
 * @author     Miloslav Hůla
 */

use Nette\Forms\Form,
	Nette\Bridges\FormsLatte\FormMacros,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$form = new Form;
$form->addText('input1', 'Input 1');

$cont1 = $form->addContainer('cont1');
$cont1->addText('input2', 'Input 2');
$cont1->addText('input3', 'Input 3');

$cont2 = $cont1->addContainer('cont2');
$cont2->addCheckbox('input4', 'Input 4');
$cont2->addCheckbox('input5', 'Input 5');
$cont2->addCheckbox('input6', 'Input 6');

$cont1->addText('input7', 'Input 7');

$form->addSubmit('input8', 'Input 8');


$latte = new Latte\Engine;
FormMacros::install($latte->getCompiler());

Assert::matchFile(
	__DIR__ . '/expected/FormMacros.formContainer.phtml',
	$latte->compile(__DIR__ . '/templates/forms.formContainer.latte')
);
Assert::matchFile(
	__DIR__ . '/expected/FormMacros.formContainer.html',
	$latte->renderToString(
		__DIR__ . '/templates/forms.formContainer.latte',
		array('_control' => array('myForm' => $form))
	)
);
