<?php

/**
 * Test: Nette\Forms\Controls\BaseControl
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Form;



require __DIR__ . '/../bootstrap.php';


test(function() { // error handling
	$form = new Form;
	$input = $form->addText('text')
		->addRule($form::EMAIL, 'error');

	Assert::same( array(), $input->getErrors() );
	Assert::null( $input->getError() );
	Assert::false( $input->hasErrors() );

	$input->validate();

	Assert::same( array('error'), $input->getErrors() );
	Assert::same( 'error', $input->getError() );
	Assert::true( $input->hasErrors() );

	$input->cleanErrors();
	Assert::false( $input->hasErrors() );
});



test(function() { // validators
	$form = new Form;
	$input = $form->addText('text');
	$input->setValue(123);

	Assert::true( $input::validateEqual($input, 123) );
	Assert::true( $input::validateEqual($input, '123') );
	Assert::true( $input::validateEqual($input, array(123, 3)) ); // "is in"
	Assert::false( $input::validateEqual($input, array('x')) );

	Assert::true( $input::validateFilled($input) );
	Assert::true( $input::validateValid($input) );
});



test(function() { // setHtmlId
	$form = new Form;
	$input = $form->addText('text')->setHtmlId('myId');

	Assert::same( '<input type="text" name="text" id="myId" value="" />', (string) $input->getControl() );
});



test(function() { // special name
	$form = new Form;
	$input = $form->addText('submit');

	Assert::same( '<input type="text" name="_submit" id="frm-submit" value="" />', (string) $input->getControl() );
});
