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

$form->addText("input");
$form->addText("ignoredInput")
	->setIgnored();

$values = $form->getValues();

Assert::true(array_key_exists("input", $values));
Assert::false(array_key_exists("ignoredInput", $values));
