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


$form = new Form('name');
$form->addProtection();
$form->addText('input');
$form->addText('omittedInput')
	->setOmitted();

Assert::same(array('input' => ''), $form->getValues(TRUE));
