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
	'name' => "invalid\xAA\xAA\xAAutf",
	'note' => "invalid\xAA\xAA\xAAutf",
	'userid' => "invalid\xAA\xAA\xAAutf",
	'secondperson' => array(NULL),
);

$tmp = array(
	'name' => 'readme.txt',
	'type' => 'text/plain',
	'tmp_name' => 'C:\\PHP\\temp\\php1D5B.tmp',
	'error' => 0,
	'size' => 209,
);

$_FILES = array(
	'name' => $tmp,
	'note' => $tmp,
	'gender' => $tmp,
	'send' => $tmp,
	'country' => $tmp,
	'countrym' => $tmp,
	'password' => $tmp,
	'firstperson' => $tmp,
	'secondperson' => array(
		'age' => $tmp,
	),
	'submit1' => $tmp,
	'userid' => $tmp,
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
	"name" => string(10) "invalidutf"
	"note" => string(10) "invalidutf"
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
	"userid" => string(10) "invalidutf"
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
