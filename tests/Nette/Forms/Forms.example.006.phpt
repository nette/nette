<?php

/**
 * Test: Nette\Forms example.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Form;



require __DIR__ . '/../bootstrap.php';



$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = array('first'=>array('name'=>'James Bond','email'=>'bond@007.com','street'=>'Unknown','city'=>'London','country'=>'GB',),'second'=>array('name'=>'Jim Beam','email'=>'jim@beam.com','street'=>'','city'=>'','country'=>'US',),'submit1'=>'Send',);


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

// group First person
$form->addGroup('First person');
$sub = $form->addContainer('first');
$sub->addText('name', 'Your name:');
$sub->addText('email', 'Email:');
$sub->addText('street', 'Street:');
$sub->addText('city', 'City:');
$sub->addSelect('country', 'Country:', $countries);

// group Second person
$form->addGroup('Second person');
$sub = $form->addContainer('second');
$sub->addText('name', 'Your name:');
$sub->addText('email', 'Email:');
$sub->addText('street', 'Street:');
$sub->addText('city', 'City:');
$sub->addSelect('country', 'Country:', $countries);

// group for buttons
$form->addGroup();

$form->addSubmit('submit', 'Send');
$form->fireEvents();

Assert::same( array(
   'first' => array(
	  'name' => 'James Bond',
	  'email' => 'bond@007.com',
	  'street' => 'Unknown',
	  'city' => 'London',
	  'country' => 'GB',
   ),
   'second' => array(
	  'name' => 'Jim Beam',
	  'email' => 'jim@beam.com',
	  'street' => '',
	  'city' => '',
	  'country' => 'US',
   ),
), $form->getValues(TRUE) );

Assert::match( file_get_contents(__DIR__ . '/Forms.example.006.expect'), $form->__toString(TRUE) );
