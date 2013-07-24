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
	array('send1', array('name', 'age', 'age2')),
	array('send2', array()),
	array('send3', array('name')),
	array('send4', array('age')),
	array('send5', array('age', 'age2')),
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

	$form->setSubmittedBy($form[$case[0]]);

	Assert::truthy($form->isSubmitted());
	$form->validate();
	Assert::equal($case[1], $form->getAllErrors());

}
