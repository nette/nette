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

$form->addText('username', 'Your username:')
	->setDisabled();
$form->addText('firstname', 'First name:');
$form->addText('lastname', 'Last name:');
$form->addSubmit('save', 'Save');

// All fields should get new value
$form->setDefaults(array(
	'username' => 'dg',
	'firstname' => 'David',
));
$form['lastname']->setDefaultValue('Grudl');
Assert::same('dg', $form['username']->getValue());
Assert::same('David', $form['firstname']->getValue());
Assert::same('Grudl', $form['lastname']->getValue());

// Submit form
$form->setSubmittedBy($form['save']);

// Only disabled fields should get new value
$form->setDefaults(array(
	'username' => 'vrana',
	'firstname' => 'Jakub',
));
$form['lastname']->setDefaultValue('Vrána');
Assert::same('vrana', $form['username']->getValue());
Assert::same('David', $form['firstname']->getValue());
Assert::same('Grudl', $form['lastname']->getValue());
