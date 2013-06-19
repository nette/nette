<?php

/**
 * Test: Nette\Forms naming container.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Form,
	Nette\ArrayHash;



require __DIR__ . '/../bootstrap.php';



$form = new Form();
$form->addText('name', 'Your name:', 35);

$sub = $form->addContainer('firstperson');
$sub->addText('name', 'Your name:', 35);
$sub->addText('age', 'Your age:', 5);

$sub = $form->addContainer('secondperson');
$sub->addText('name', 'Your name:', 35);
$sub->addText('age', 'Your age:', 5);
$sub->addUpload('avatar', 'Picture:');

$form->addText('age', 'Your age:', 5);

$form->addSubmit('submit1', 'Send');

$form->setDefaults(array(
	'name' => 'jim',
	'firstperson' =>
	array(
		'name' => 'david',
		'age' => '30',
	),
	'secondperson' =>
	array(
		'name' => 'jim',
		'age' => '40',
	),
	'age' => '50',
));

Assert::equal( ArrayHash::from(array(
	"name" => "jim",
	"firstperson" => ArrayHash::from(array(
		"name" => "david",
		"age" => "30",
	)),
	"secondperson" => ArrayHash::from(array(
		"name" => "jim",
		"age" => "40",
		"avatar" => NULL,
	)),
	"age" => "50",
)), $form->getValues() );
