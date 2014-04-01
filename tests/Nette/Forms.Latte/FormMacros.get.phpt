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


$form = new Form;
$form->setMethod('get');
$form->setAction('?arg=val');
$form->addSubmit('send', 'Sign in');

$latte = new Latte\Engine;
FormMacros::install($latte->getCompiler());

Assert::matchFile(
	__DIR__ . '/expected/FormMacros.get.phtml',
	$latte->compile(__DIR__ . '/templates/forms.get.latte')
);
Assert::matchFile(
	__DIR__ . '/expected/FormMacros.get.html',
	$latte->renderToString(
		__DIR__ . '/templates/forms.get.latte',
		array('_control' => array('myForm' => $form))
	)
);
