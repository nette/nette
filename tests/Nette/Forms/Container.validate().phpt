<?php

/**
 * Test: Nette\Forms\Container::validate().
 *
 * @author     Filip Procházka
 * @package    Nette\Forms
 */

use Nette\Forms\Form;
use Nette\Forms\Container;



require __DIR__ . '/../bootstrap.php';

$form = new Form;
$form->addText('name', 'Text:', 10)->addRule($form::NUMERIC);
$form->onValidate[] = function (Container $container) {
	$container['name']->addError('just fail');
};

$form->setValues(array('name' => "invalid*input"));
$form->validate();

Assert::same(array(
	'Please enter a numeric value.',
	'just fail',
), $form['name']->getErrors());
