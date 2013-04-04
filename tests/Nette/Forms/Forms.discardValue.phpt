<?php

/**
 * Test: Nette\Forms ignored input.
 *
 * @author     Roman Pavlík
 * @package    Nette\Forms
 */

use Nette\Forms\Form,
	Nette\ArrayHash;



require __DIR__ . '/../bootstrap.php';


$form = new Form();

$form->addText("validInput");
$form->addText("discardedInput")
	->setDiscarded();

$values = $form->getValues();

Assert::true(array_key_exists("validInput", $values));
Assert::false(array_key_exists("discardedInput", $values));
