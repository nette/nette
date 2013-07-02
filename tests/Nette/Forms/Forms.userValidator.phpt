<?php

/**
 * Test: Nette\Forms user validator.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Form;


require __DIR__ . '/../bootstrap.php';


$datasets = array(
	array(11, array('Value 11 is not allowed!')),
	array(22, array()),
	array(1, array('Value 22 is required!')),
);

function myValidator1($item, $arg)
{
	return $item->getValue() != $arg;
}


foreach ($datasets as $case) {

	$form = new Form;
	$control = $form->addText('value', 'Value:')
		->addRule('myValidator1', 'Value %d is not allowed!', 11)
		->addRule(~'myValidator1', 'Value %d is required!', 22);

	$control->setValue($case[0])->validate();
	Assert::same($case[1], $control->getErrors());
}
