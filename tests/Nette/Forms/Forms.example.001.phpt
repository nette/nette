<?php

/**
 * Test: Nette\Forms example.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 * @subpackage UnitTests
 */

use Nette\Forms\Form,
	Nette\Utils\Html;



require __DIR__ . '/../bootstrap.php';



$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = array('name'=>'John Doe ','age'=>'','email'=>'  @ ','send'=>'on','street'=>'','city'=>'','country'=>'HU','password'=>'xxx','password2'=>'','note'=>'','submit1'=>'Send','userid'=>'231',);


$countries = array(
	'Select your country',
	'Europe' => array(
		'CZ' => 'Czech Republic',
		'SK' => 'Slovakia',
		'GB' => 'United Kingdom',
	),
	'CA' => 'Canada',
	'US' => 'United States',
	'?'  => 'other',
);

$sex = array(
	'm' => 'male',
	'f' => 'female',
);



// Step 1: Define form with validation rules
$form = new Form;

// group Personal data
$form->addGroup('Personal data')
	->setOption('description', 'We value your privacy and we ensure that the information you give to us will not be shared to other entities.');

$form->addText('name', 'Your name:')
	->addRule(Form::FILLED, 'Enter your name');

$form->addText('age', 'Your age:')
	->addRule(Form::FILLED, 'Enter your age')
	->addRule(Form::INTEGER, 'Age must be numeric value')
	->addRule(Form::RANGE, 'Age must be in range from %d to %d', array(10, 100));

$form->addRadioList('gender', 'Your gender:', $sex);

$form->addText('email', 'Email:')
	->setEmptyValue('@')
	->addCondition(Form::FILLED) // conditional rule: if is email filled, ...
		->addRule(Form::EMAIL, 'Incorrect email address'); // ... then check email


// group Shipping address
$form->addGroup('Shipping address')
	->setOption('embedNext', TRUE);

$form->addCheckbox('send', 'Ship to address')
	->addCondition(Form::EQUAL, TRUE) // conditional rule: if is checkbox checked...
		->toggle('sendBox'); // toggle div #sendBox


// subgroup
$form->addGroup()
	->setOption('container', Html::el('div')->id('sendBox'));

$form->addText('street', 'Street:');

$form->addText('city', 'City:')
	->addConditionOn($form['send'], Form::EQUAL, TRUE)
		->addRule(Form::FILLED, 'Enter your shipping address');

$form->addSelect('country', 'Country:', $countries)
	->skipFirst()
	->addConditionOn($form['send'], Form::EQUAL, TRUE)
		->addRule(Form::FILLED, 'Select your country');


// group Your account
$form->addGroup('Your account');

$form->addPassword('password', 'Choose password:')
	->addRule(Form::FILLED, 'Choose your password')
	->addRule(Form::MIN_LENGTH, 'The password is too short: it must be at least %d characters', 3);

$form->addPassword('password2', 'Reenter password:')
	->addConditionOn($form['password'], Form::VALID)
		->addRule(Form::FILLED, 'Reenter your password')
		->addRule(Form::EQUAL, 'Passwords do not match', $form['password']);

$form->addFile('avatar', 'Picture:')
	->addCondition(Form::FILLED)
		->addRule(Form::IMAGE, 'Uploaded file is not image');

$form->addHidden('userid');

$form->addTextArea('note', 'Comment:');


// group for buttons
$form->addGroup();

$form->addSubmit('submit', 'Send');



$defaults = array(
	'name'    => 'John Doe',
	'userid'  => 231,
	'country' => 'CZ', // Czech Republic
);

$form->setDefaults($defaults);
$form->fireEvents();

Assert::match( file_get_contents(__DIR__ . '/Forms.example.001.expect'), $form->__toString(TRUE) );
