<?php

/**
 * Test: Nette\Forms setDefaultValue and setDefaults test.
 *
 * @author     Jáchym Toušek
 * @package    Nette\Forms
 */

use Nette\Forms\Form;



require __DIR__ . '/../bootstrap.php';



$form = new Form;

$form->addText('a')
	->setDisabled();
$form->addText('b')
	->setDisabled();
$form->addText('c');
$form->addContainer('cont')
	->addText('d')
		->setDisabled();
$form->addSubmit('save', 'Save');

// All fields should get new value
$form->setDefaults(array(
	'b' => 'value-b',
	'c' => 'value-c',
	'cont' => array(
		'd' => 'value-d',
	),
));
$form['a']->setDefaultValue('value-a');

Assert::same('value-a', $form['a']->getValue());
Assert::same('value-b', $form['b']->getValue());
Assert::same('value-c', $form['c']->getValue());
Assert::same('value-d', $form['cont-d']->getValue());


// Submit form
$form->setSubmittedBy($form['save']);

// Only disabled fields should get new value
$form->setDefaults(array(
	'b' => 'new',
	'c' => 'new',
	'cont' => array(
		'd' => 'new',
	),
));
$form['a']->setDefaultValue('new');

Assert::same('new', $form['a']->getValue());
Assert::same('new', $form['b']->getValue());
Assert::same('value-c', $form['c']->getValue());
Assert::same('new', $form['cont-d']->getValue());
