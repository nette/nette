<?php

/**
 * Test: Nette\Forms\Container::validate().
 *
 * @author     Filip Procházka
 */

use Nette\Forms\Form,
	Nette\Forms\Container,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

$form = new Form;
$form->addText('name', 'Text:')->addRule($form::NUMERIC);
$form->onValidate[] = function (Container $container) {
	$container['name']->addError('just fail');
};

$form->setValues(array('name' => "invalid*input"));
$form->validate();

Assert::same(array(
	'Please enter a valid integer.',
	'just fail',
), $form['name']->getErrors());
