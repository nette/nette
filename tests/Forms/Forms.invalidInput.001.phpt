<?php

/**
 * Test: Nette\Forms invalid input.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Forms
 * @subpackage UnitTests
 */

use Nette\Forms\Form;



require __DIR__ . '/../NetteTest/initialize.php';



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
	'Select your country',
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
$form->addSelect('country', 'Country:', $countries)->skipFirst();
$form->addMultiSelect('countrym', 'Country:', $countries);
$form->addPassword('password', 'Choose password:', 20);
$form->addFile('avatar', 'Picture:');
$form->addHidden('userid');

$sub = $form->addContainer('firstperson');
$sub->addText('age', 'Your age:', 5);

$sub = $form->addContainer('secondperson');
$sub->addText('age', 'Your age:', 5);
$sub->addFile('avatar', 'Picture:');

$form->addSubmit('submit1', 'Send');

dump( (bool) $form->isSubmitted() );
dump( $form->getValues() );



__halt_compiler() ?>

------EXPECT------
bool(TRUE)

array(11) {
	"name" => string(0) ""
	"note" => string(0) ""
	"gender" => NULL
	"send" => bool(FALSE)
	"country" => NULL
	"countrym" => array(0)
	"password" => string(0) ""
	"avatar" => object(%ns%HttpUploadedFile) (5) {
		"name" private => NULL
		"type" private => NULL
		"size" private => NULL
		"tmpName" private => NULL
		"error" private => int(4)
	}
	"userid" => string(0) ""
	"firstperson" => array(1) {
		"age" => string(0) ""
	}
	"secondperson" => array(2) {
		"age" => string(0) ""
		"avatar" => object(%ns%HttpUploadedFile) (5) {
			"name" private => NULL
			"type" private => NULL
			"size" private => NULL
			"tmpName" private => NULL
			"error" private => int(4)
		}
	}
}
