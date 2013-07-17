<?php

/**
 * Test: Nette\Forms default rendering.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Utils\Html,
	Nette\Forms\Form;


require __DIR__ . '/../bootstrap.php';


$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = array('name'=>'John Doe ','age'=>'9.9','email'=>'@','street'=>'','city'=>'Troubsko','country'=>'0','password'=>'xx','password2'=>'xx','note'=>'','submit1'=>'Send','userid'=>'231',);


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
	'm' => Html::el('option', 'male')->style('color: #248bd3'),
	'f' => Html::el('option', 'female')->style('color: #e948d4'),
);


$form = new Form;

$renderer = $form->renderer;
$renderer->wrappers['form']['container'] = Html::el('div')->id('form');
$renderer->wrappers['form']['errors'] = FALSE;
$renderer->wrappers['group']['container'] = NULL;
$renderer->wrappers['group']['label'] = 'h3';
$renderer->wrappers['pair']['container'] = NULL;
$renderer->wrappers['controls']['container'] = 'dl';
$renderer->wrappers['control']['container'] = 'dd';
$renderer->wrappers['control']['.odd'] = 'odd';
$renderer->wrappers['control']['errors'] = TRUE;
$renderer->wrappers['label']['container'] = 'dt';
$renderer->wrappers['label']['suffix'] = ':';
$renderer->wrappers['control']['requiredsuffix'] = " \xE2\x80\xA2";


$form->addGroup('Personal data');
$form->addText('name', 'Your name')
	->addRule(Form::FILLED, 'Enter your name');

$form->addText('age', 'Your age')
	->addRule(Form::FILLED, 'Enter your age')
	->addRule(Form::INTEGER, 'Age must be numeric value')
	->addRule(Form::RANGE, 'Age must be in range from %d to %d', array(10, 100));

$form->addSelect('gender', 'Your gender', $sex);

$form->addText('email', 'Email')
	->setEmptyValue('@')
	->addCondition(Form::FILLED)
		->addRule(Form::EMAIL, 'Incorrect email address');


$form->addGroup('Shipping address')
	->setOption('embedNext', TRUE);

$form->addCheckbox('send', 'Ship to address')
	->addCondition(Form::EQUAL, TRUE)
		->toggle('sendBox');


$form->addGroup()
	->setOption('container', Html::el('div')->id('sendBox'));

$form->addText('street', 'Street');

$form->addText('city', 'City')
	->addConditionOn($form['send'], Form::EQUAL, TRUE)
		->addRule(Form::FILLED, 'Enter your shipping address');

$form->addSelect('country', 'Country', $countries)
	->setPrompt('Select your country')
	->addConditionOn($form['send'], Form::EQUAL, TRUE)
		->addRule(Form::FILLED, 'Select your country');


$form->addGroup('Your account');

$form->addPassword('password', 'Choose password')
	->addRule(Form::FILLED, 'Choose your password')
	->addRule(Form::MIN_LENGTH, 'The password is too short: it must be at least %d characters', 3)
	->setOption('description', '(at least 3 characters)');

$form->addPassword('password2', 'Reenter password')
	->addConditionOn($form['password'], Form::VALID)
		->addRule(Form::FILLED, 'Reenter your password')
		->addRule(Form::EQUAL, 'Passwords do not match', $form['password']);

$form->addUpload('avatar', 'Picture');

$form->addHidden('userid');

$form->addTextArea('note', 'Comment');


$form->addGroup();

$form->addSubmit('submit', 'Send');


$defaults = array(
	'name'    => 'John Doe',
	'userid'  => 231,
	'country' => 'CZ',
);

$form->setDefaults($defaults);
$form->fireEvents();

Assert::matchFile(__DIR__ . '/Forms.renderer.2.expect', $form->__toString(TRUE) );
