<?php

/**
 * Test: Nette\Forms invalid input.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Http,
	Nette\Forms\Form;



require __DIR__ . '/../bootstrap.php';



$_SERVER['REQUEST_METHOD'] = 'POST';

$_POST = array(
	'name' => array(NULL),
	'note' => array(NULL),
	'gender' => array(NULL),
	'send' => array(NULL),
	'country' => array(NULL),
	'countrym' => '',
	'password' => array(NULL),
	'firstperson' => TRUE,
	'secondperson' => array(
		'age' => array(NULL),
	),
	'submit1' => array(NULL),
	'userid' => array(NULL),
);


$_FILES = array(
	'avatar' => array(
		'name' => 'readme.txt',
		'type' => 'text/plain',
	),
	'secondperson' => array(
		'name' => array(NULL),
		'type' => array(NULL),
		'tmp_name' => array(NULL),
		'error' => array(NULL),
		'size' => array(NULL),
	),
);

$countries = array(
	'Europe' => array(
		1 => 'Czech Republic',
		2 => 'Slovakia',
	),
	3 => 'USA',
	4 => 'other',
);

$sex = array(
	'm' => 'male',
	'f' => 'female',
);

$form = new Form();
$form->addText('name', 'Your name:', 35);  // item name, label, size, maxlength
$form->addTextArea('note', 'Comment:', 30, 5);
$form->addRadioList('gender', 'Your gender:', $sex);
$form->addCheckbox('send', 'Ship to address');
$form->addSelect('country', 'Country:', $countries)->setPrompt('Select your country');
$form->addMultiSelect('countrym', 'Country:', $countries);
$form->addPassword('password', 'Choose password:', 20);
$form->addUpload('avatar', 'Picture:');
$form->addHidden('userid');

$sub = $form->addContainer('firstperson');
$sub->addText('age', 'Your age:', 5);

$sub = $form->addContainer('secondperson');
$sub->addText('age', 'Your age:', 5);
$sub->addUpload('avatar', 'Picture:');

$form->addSubmit('submit1', 'Send');

Assert::true( (bool) $form->isSubmitted() );
Assert::equal( array(
	'name' => '',
	'note' => '',
	'gender' => NULL,
	'send' => FALSE,
	'country' => NULL,
	'countrym' => array(),
	'password' => '',
	'avatar' => new Http\FileUpload(array()),
	'userid' => '',
	'firstperson' => array(
		'age' => '',
	),
	'secondperson' => array(
		'age' => '',
		'avatar' => new Http\FileUpload(array()),
	),
), $form->getValues(TRUE) );
