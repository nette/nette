<?php

/**
 * Test: Nette\Forms validation of range depends on another control.
 *
 * @author     Karel Hak
 */

use Nette\Forms\Form,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$datasets = array(
	array(array('min' => '10', 'max' => '20', 'value' => 5), FALSE),
	array(array('min' => '10', 'max' => '20', 'value' => 15), TRUE),
	array(array('min' => '10', 'max' => '', 'value' => 15), TRUE),
	array(array('min' => '10', 'max' => '', 'value' => 5), FALSE),
);

foreach ($datasets as $case) {

	$form = new Form;

	$form->addText('min');
	$form->addText('max');
	$form->addText('value')->addRule(Form::RANGE, NULL, array($form['min'], $form['max']));
	$form->setValues($case[0]);

	Assert::equal($case[1], $form->isValid());
}
