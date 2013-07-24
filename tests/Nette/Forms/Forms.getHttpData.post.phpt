<?php

/**
 * Test: Nette\Forms HTTP data.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Form,
	Nette\Forms\Validator;


require __DIR__ . '/../bootstrap.php';


before(function() {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_GET = $_POST = $_FILES = array();
});


test(function() {
	$form = new Form;
	$form->addSubmit('send', 'Send');

	Assert::truthy( $form->isSubmitted() );
	Assert::true( $form->isSuccess() );
	Assert::same( array(), $form->getHttpData() );
	Assert::same( array(), $form->getValues(TRUE) );
});


test(function() {
	$form = new Form;
	$form->setMethod($form::GET);
	$form->addSubmit('send', 'Send');

	Assert::false( $form->isSubmitted() );
	Assert::false( $form->isSuccess() );
	Assert::same( array(), $form->getHttpData() );
	Assert::same( array(), $form->getValues(TRUE) );
});


test(function() {
	$name = 'name';
	$_POST = array(Form::TRACKER_ID => $name);

	$form = new Form($name);
	$form->addSubmit('send', 'Send');

	Assert::truthy( $form->isSubmitted() );
	Assert::same( array(Form::TRACKER_ID => $name), $form->getHttpData() );
	Assert::same( array(), $form->getValues(TRUE) );
	Assert::same( $name, $form[Form::TRACKER_ID]->getValue() );
});


test(function() {
	$form = new Form;
	$input = $form->addSubmit('send', 'Send');
	Assert::false( $input->isSubmittedBy() );
	Assert::false( Validator::validateSubmitted($input) );

	$_POST = array('send' => '');
	$form = new Form;
	$input = $form->addSubmit('send', 'Send');
	Assert::true( $input->isSubmittedBy() );
	Assert::true( Validator::validateSubmitted($input) );
});
