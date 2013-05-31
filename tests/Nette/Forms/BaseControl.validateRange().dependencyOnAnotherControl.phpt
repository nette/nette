<?php

/**
 * Test: Nette\Forms validation of range depends on another control.
 *
 * @author     Karel Hak
 * @package    Nette\Forms
 */

use Nette\Forms\Form;
use Tester\Assert;



require __DIR__ . '/../bootstrap.php';



$datasets = array(
	array(array('min' => '10', 'max' => '20', 'value' => 35), false),
	array(array('min' => '10', 'max' => '20', 'value' => 5), false),
	array(array('min' => '10', 'max' => '20', 'value' => 15), true),
	array(array('min' => '10', 'max' => '', 'value' => 15), true),
	array(array('min' => '', 'max' => '20', 'value' => 15), true),
	array(array('min' => '', 'max' => '', 'value' => 15), true),
);

foreach ($datasets as $case) {

	$form = new Form;

	$form->addText('min');
	$form->addText('max');
	$form->addText('value')->addRule(Form::RANGE, null, array($form['min'], $form['max']));
	$form->setValues($case[0]);

	Assert::equal($case[1], $form->isValid());

}
