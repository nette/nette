<?php

/**
 * Test: Nette\Forms isValid.
 */

use Nette\Forms\Form,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


before(function() {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_GET = $_POST = $_FILES = array();
});


test(function() {
	$form = new Form;
	$form->addText('item');

	Assert::true( $form->isSubmitted() );
	Assert::true( $form->isValid() );
	Assert::true( $form->isSuccess() );
	Assert::same( array(), $form->getErrors() );

	$form['item']->addError('1');

	Assert::true( $form->isSubmitted() );
	Assert::false( $form->isValid() );
	Assert::false( $form->isSuccess() );
	Assert::same( array('1'), $form->getErrors() );

	$form['item']->addError('2');

	Assert::true( $form->isSubmitted() );
	Assert::false( $form->isValid() );
	Assert::same( array('1', '2'), $form->getErrors() );

	$form->validate();

	Assert::true( $form->isSubmitted() );
	Assert::true( $form->isValid() );
	Assert::same( array(), $form->getErrors() );
});


test(function() {
	$form = new Form;
	$form->addText('item');

	$form->addError('1');

	Assert::true( $form->isSubmitted() );
	Assert::false( $form->isValid() );
	Assert::same( array('1'), $form->getErrors() );
});


test(function() {
	$form = new Form;
	$form->addText('item');

	$form['item']->addError('1');

	Assert::true( $form->isSubmitted() );
	Assert::false( $form->isValid() );
	Assert::same( array('1'), $form->getErrors() );
});


test(function() {
	$form = new Form;
	$form->addText('item');

	$form->addError('1');
	$form['item']->addError('2');

	Assert::true( $form->isSubmitted() );
	Assert::false( $form->isValid() );
	Assert::same( array('1', '2'), $form->getErrors() );
});


test(function() {
	$form = new Form;
	$form->addText('item');

	$form->addError('1');
	$form['item']->addError('2');
	$form->fireEvents();

	Assert::true( $form->isSubmitted() );
	Assert::false( $form->isValid() );
	Assert::same( array('1', '2'), $form->getErrors() );
});
