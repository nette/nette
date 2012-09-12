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
$_POST = array('num1'=>'5','num2'=>'5','submit1'=>'Send',);



function myValidator($item, $arg)
{
	return $item->value % $arg === 0;
}



// Step 1: Define form with validation rules
$form = new Form;

$form->addText('num1', 'Multiple of 8:')
	->addRule('myValidator', 'First number must be %d multiple', 8);

$form->addText('num2', 'Not multiple of 5:')
	->addRule(~'myValidator', 'Second number must not be %d multiple', 5); // negative


$form->addSubmit('submit', 'Send');


$defaults = array(
	'num1'    => '5',
	'num2'    => '5',
);

$form->setDefaults($defaults);
$form->fireEvents();

Assert::match( file_get_contents(__DIR__ . '/Forms.example.005.expect'), $form->__toString(TRUE) );
