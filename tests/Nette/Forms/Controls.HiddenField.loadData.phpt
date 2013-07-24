<?php

/**
 * Test: Nette\Forms\Controls\HiddenField.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Form;


require __DIR__ . '/../bootstrap.php';


before(function() {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = $_FILES = array();
});


test(function() {
	$_POST = array('text' => "  a\r b \n c ");
	$form = new Form;
	$input = $form->addHidden('text');
	Assert::same( "  a\n b \n c ", $input->getValue() );
	Assert::true( $input->isFilled() );
});


test(function() {
	$form = new Form;
	$input = $form->addText('unknown');
	Assert::same( '', $input->getValue() );
	Assert::false( $input->isFilled() );
});


test(function() { // invalid data
	$_POST = array('malformed' => array(NULL));
	$form = new Form;
	$input = $form->addHidden('malformed');
	Assert::same( '', $input->getValue() );
	Assert::false( $input->isFilled() );
});


test(function() { // errors are moved to form
	$form = new Form;
	$input = $form->addHidden('hidden');
	$input->addError('error');
	Assert::same( array(), $input->getErrors() );
	Assert::same( array('error'), $form->getErrors() );
});


test(function() { // setValue() and invalid argument
	$form = new Form;
	$input = $form->addHidden('hidden');
	$input->setValue(NULL);

	Assert::exception(function() use ($input) {
		$input->setValue(array());
	}, 'Nette\InvalidArgumentException', "Value must be scalar or NULL, array given.");
});


test(function() { // object
	$form = new Form;
	$input = $form->addHidden('hidden')
		->setValue(new Nette\DateTime('2013-07-05'));

	Assert::same( '2013-07-05 00:00:00', $input->getValue() );
});
