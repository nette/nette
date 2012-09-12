<?php

/**
 * Test: Nette\Forms naming container.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Http,
	Nette\Forms\Form,
	Nette\ArrayHash;



require __DIR__ . '/../bootstrap.php';



$_SERVER['REQUEST_METHOD'] = 'POST';

$_POST = array(
	'name' => 'jim',
	'text1' => 'hello',
	'text2' => 'world',
	'formCont' =>
	array(
		'name' => 'jack',
		'age' => '23',
	),
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
);

$_FILES = array(
	'secondperson' => array(
		'name' => array(
			'avatar' => 'license.txt',
		),

		'type' => array(
			'avatar' => 'text/plain',
		),

		'tmp_name' => array(
			'avatar' => 'C:\\PHP\\temp\\php1D5C.tmp',
		),

		'error' => array(
			'avatar' => 0,
		),

		'size' => array(
			'avatar' => 3013,
		),
	),
);



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

Assert::true( (bool) $form->isSubmitted() );
Assert::equal( ArrayHash::from(array(
	'name' => 'jim',
	'firstperson' => ArrayHash::from(array(
		'name' => 'david',
		'age' => '30',
	)),
	'secondperson' => ArrayHash::from(array(
		'name' => 'jim',
		'age' => '40',
		'avatar' => new Http\FileUpload(array(
			'name' => 'license.txt',
			'type' => '',
			'size' => 3013,
			'tmp_name' => 'C:\PHP\temp\php1D5C.tmp',
			'error' => 0,
		)),
	)),
	'age' => '50',
)), $form->getValues() );
