<?php

/**
 * Test: Nette\Forms validation scope.
 *
 * @author     Jan Skrasek
 * @package    Nette\Forms
 */

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$datasets = array(
	array(array('send1' => 'send1'), array('name', 'age', 'age2')),
	array(array('send2' => 'send2'), array()),
	array(array('send3' => 'send3'), array('name')),
	array(array('send4' => 'send4'), array('age')),
	array(array('send5' => 'send5'), array('age', 'age2')),
);

foreach ($datasets as $case) {

	$form = new Form;
	$form->addText('name')->setRequired('name');

	$details = $form->addContainer('details');
	$details->addText('age')->setRequired('age');
	$details->addText('age2')->setRequired('age2');

	$form->addSubmit('send1');
	$form->addSubmit('send2')->setValidationScope(FALSE);
	$form->addSubmit('send3')->setValidationScope(array($form['name']));
	$form->addSubmit('send4')->setValidationScope(array($form['details']['age']));
	$form->addSubmit('send5')->setValidationScope(array($form['details']));

	$form->setValues($case[0]);

	Assert::true((bool) $form->isSubmitted());
	$form->validate();
	Assert::equal($case[1], $form->getAllErrors());

}
