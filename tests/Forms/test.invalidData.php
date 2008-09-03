<h1>Nette::Forms invalid HTTP data test</h1>

<?php

require_once '../../Nette/loader.php';

/*use Nette::ComponentContainer;*/
/*use Nette::Forms::Form;*/
/*use Nette::Forms::TextInput;*/
/*use Nette::Forms::FormContainer;*/
/*use Nette::Debug;*/

Debug::enable();

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



$_SERVER['REQUEST_METHOD'] = 'POST';

/* valid data
$_POST = array(
	'name' => 'string',
	'note' => 'textbox',
	'gender' => 'm',
	'send' => 'on',
	'country' => '1',
	'countrym' => array(
		0 => '0',
		1 => '1',
		2 => '2',
	),
	'password' => 'string',
	'firstperson' => array(
		'age' => 'string',
	),
	'secondperson' => array(
		'age' => 'string',
	),
	'submit1' => 'Send',
	'userid' => '',
);


$_FILES = array(
	'avatar' => array(
		'name' => 'readme.txt',
		'type' => 'text/plain',
		'tmp_name' => 'C:\\PHP\\temp\\php1D5B.tmp',
		'error' => 0,
		'size' => 209,
	),
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
*/


// invalid #1
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

echo "<h2>Invalid data #1</h2>\n";

echo "Submitted?\n";
$dolly = clone($form);
Debug::dump(gettype($dolly->isSubmitted()));

echo "Values:\n";
Debug::dump($dolly->getValues());





// invalid #2
$_POST = array(
	'secondperson' => array(NULL),
);


$_FILES = array(
	'avatar' => array(
		'name' => 'readme.txt',
		'type' => 'text/plain',
		'tmp_name' => 'C:\\PHP\\temp\\php1D5B.tmp',
		'error' => 0,
		'size' => 209,
	),
);
$_FILES = array(
	'name' => $_FILES['avatar'],
	'note' => $_FILES['avatar'],
	'gender' => $_FILES['avatar'],
	'send' => $_FILES['avatar'],
	'country' => $_FILES['avatar'],
	'countrym' => $_FILES['avatar'],
	'password' => $_FILES['avatar'],
	'firstperson' => $_FILES['avatar'],
	'secondperson' => array(
		'age' => $_FILES['avatar'],
	),
	'submit1' => $_FILES['avatar'],
	'userid' => $_FILES['avatar'],
);


echo "<h2>Invalid data #2</h2>\n";

echo "Submitted?\n";
$dolly = clone($form);
Debug::dump(gettype($dolly->isSubmitted()));

echo "Values:\n";
Debug::dump($dolly->getValues());
