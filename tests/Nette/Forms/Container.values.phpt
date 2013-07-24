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
	'first' => array(
		'name' => 'jim',
		'age' => '40',
		'second' => array(
			'name' => 'david',
		),
	),
	'invalid' => TRUE,
);


$first = new Nette\Forms\Container;
$first->addText('name');
$first->addText('age');

$second = $first->addContainer('second');
$second->addText('name');

$first->setDefaults(array(
	'name' => 'xxx',
	'age' => '50',
	'second' => array(
		'name' => 'yyy',
		'age' => '30',
	),
));

Assert::equal( ArrayHash::from(array(
	'name' => 'xxx',
	'age' => '50',
	'second' => ArrayHash::from(array(
		'name' => 'yyy',
	)),
)), $first->getValues() );


$form = new Form;
$form->addText('name');
$form['first'] = $first;
$invalid = $form->addContainer('invalid');
$invalid->addText('name');
$form->addSubmit('send');


Assert::truthy( $form->isSubmitted() );
Assert::equal( ArrayHash::from(array(
	'name' => 'jim',
	'first' => ArrayHash::from(array(
		'name' => 'jim',
		'age' => '40',
		'second' => ArrayHash::from(array(
			'name' => 'david',
		)),
	)),
	'invalid' => ArrayHash::from(array(
		'name' => '',
	)),
)), $form->getValues() );
