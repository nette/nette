<?php

/**
 * Test: Nette\Forms HTTP data.
 */

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


before(function() {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_GET = $_POST = $_FILES = array();
});


test(function() {
	$name = 'name';
	$_POST = array(Form::TRACKER_ID => $name, 'send2' => '');

	$form = new Form($name);
	$btn1 = $form->addSubmit('send1');
	$btn2 = $form->addSubmit('send2');
	$btn3 = $form->addSubmit('send3');

	Assert::true( $form->isSuccess() );
	Assert::same( $btn2, $form->isSubmitted() );
});


test(function() {
	$name = 'name';
	$_POST = array(Form::TRACKER_ID => $name, 'send2' => array('x' => 1, 'y' => 1));

	$form = new Form($name);
	$btn1 = $form->addImage('send1');
	$btn2 = $form->addImage('send2');
	$btn3 = $form->addImage('send3');

	Assert::true( $form->isSuccess() );
	Assert::same( $btn2, $form->isSubmitted() );
});
