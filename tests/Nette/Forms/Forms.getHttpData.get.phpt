<?php

/**
 * Test: Nette\Forms HTTP data.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Form;


require __DIR__ . '/../bootstrap.php';


before(function() {
	$_SERVER['REQUEST_METHOD'] = 'GET';
	$_GET = $_POST = $_FILES = array();
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
	$form = new Form;
	$form->addSubmit('send', 'Send');

	Assert::false( $form->isSubmitted() );
	Assert::same( array(), $form->getHttpData() );
	Assert::same( array(), $form->getValues(TRUE) );
});


test(function() {
	$name = 'name';
	$_GET = array(Form::TRACKER_ID => $name);

	$form = new Form($name);
	$form->setMethod($form::GET);
	$form->addSubmit('send', 'Send');

	Assert::truthy( $form->isSubmitted() );
	Assert::same( array(Form::TRACKER_ID => $name), $form->getHttpData() );
	Assert::same( array(), $form->getValues(TRUE) );
	Assert::same( $name, $form[Form::TRACKER_ID]->getValue() );
});
