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
	array(11, false, 'Value 11 is not allowed!'),
	array(22, true, null),
	array(1, false, 'Value 22 is required!'),
);

function myValidator1($item, $arg)
{
	return $item->getValue() != $arg;
}


foreach ($datasets as $case) {	

	$form = new Form();
	$control = $form->addText('value', 'Value:', 10)
		->addRule('myValidator1', 'Value %d is not allowed!', 11)
		->addRule(~'myValidator1', 'Value %d is required!', 22);
   
	$isValid = $control->setValue($case[0])->getRules()->validate($onlyCheck = false);
	Assert::equal($case[1], $isValid);
	
	if (!$isValid) {
		$errors = $control->getErrors();
		Assert::equal($case[2], $errors[0]);
	}
}