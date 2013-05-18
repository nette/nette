<?php

/**
 * Test: Nette\Forms setDefaultValue test.
 *
 * @author     Jáchym Toušek
 * @package    Nette\Forms
 */

use Nette\Forms\Form;



require __DIR__ . '/../bootstrap.php';



$form = new Form;

$form->addText('a')
	->setDisabled()
	->setDefaultValue('old');
$form->addSubmit('save');

Assert::same('old', $form['a']->getValue());

// Submit form
$form->setSubmittedBy($form['save']);

// disabled fields should get new value
$form['a']->setDefaultValue('new');

Assert::same('new', $form['a']->getValue());
