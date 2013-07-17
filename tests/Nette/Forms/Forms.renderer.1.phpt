<?php

/**
 * Test: Nette\Forms default rendering.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Form,
	Nette\Utils\Html;


require __DIR__ . '/../bootstrap.php';


$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = array('name'=>'John Doe ','age'=>'','email'=>'  @ ','send'=>'on','street'=>'','city'=>'','country'=>'HU','password'=>'xxx','password2'=>'','note'=>'','submit1'=>'Send','userid'=>'231',);


$countries = array(
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


$form = new Form;


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
	->addCondition(Form::FILLED)
		->addRule(Form::EMAIL, 'Incorrect email address');


$form->addGroup('Shipping address')
	->setOption('embedNext', TRUE);

$form->addCheckbox('send', 'Ship to address')
	->addCondition(Form::EQUAL, FALSE)
	->elseCondition()
		->toggle('sendBox');


$form->addGroup()
	->setOption('container', Html::el('div')->id('sendBox'));

$form->addText('street', 'Street:');

$form->addText('city', 'City:')
	->addConditionOn($form['send'], Form::EQUAL, TRUE)
		->addRule(Form::FILLED, 'Enter your shipping address');

$form->addSelect('country', 'Country:', $countries)
	->setPrompt('Select your country')
	->addConditionOn($form['send'], Form::EQUAL, TRUE)
		->addRule(Form::FILLED, 'Select your country');

$form->addSelect('countrySetItems', 'Country:')
	->setPrompt('Select your country')
	->setItems($countries);


$form->addGroup('Your account');

$form->addPassword('password', 'Choose password:')
	->addRule(Form::FILLED, 'Choose your password')
	->addRule(Form::MIN_LENGTH, 'The password is too short: it must be at least %d characters', 3);

$form->addPassword('password2', 'Reenter password:')
	->addConditionOn($form['password'], Form::VALID)
		->addRule(Form::FILLED, 'Reenter your password')
		->addRule(Form::EQUAL, 'Passwords do not match', $form['password']);

$form->addUpload('avatar', 'Picture:')
	->addCondition(Form::FILLED)
		->addRule(Form::IMAGE, 'Uploaded file is not image');

$form->addHidden('userid');

$form->addTextArea('note', 'Comment:');

$form->addButton('unset');
unset($form['unset']);


$form->addGroup();

$form->addSubmit('submit', 'Send');
$form->addButton('cancel', 'Cancel');


$defaults = array(
	'name'    => 'John Doe',
	'userid'  => 231,
	'country' => 'CZ',
);

$form->setDefaults($defaults);
$form->fireEvents();

Assert::matchFile(__DIR__ . '/Forms.renderer.1.expect', $form->__toString(TRUE) );
