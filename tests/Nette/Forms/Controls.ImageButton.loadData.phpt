<?php

/**
 * Test: Nette\Forms\Controls\ImageButton.
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
	$_POST = array(
	'image' => array(1, 2),
	'container' => array(
		'image' => array(3, 4),
	),
	);

	$form = new Form;
	$input = $form->addImage('image');
	Assert::true( $input->isFilled() );
	Assert::same( array(1, 2), $input->getValue() );

	$input = $form->addContainer('container')->addImage('image');
	Assert::same( array(3, 4), $form['container']['image']->getValue() );
});


test(function() { // missing data
	$form = new Form;
	$input = $form->addImage('missing');
	Assert::false( $input->isFilled() );
	Assert::false( $input->getValue() );
});


test(function() { // malformed data
	$_POST = array(
		'malformed1' => array(1),
		'malformed2' => array(array(NULL), 'x'),
	);

	$form = new Form;
	$input = $form->addImage('malformed1');
	Assert::true( $input->isFilled() );
	Assert::same( array(1, 0), $input->getValue() );

	$input = $form->addImage('malformed2');
	Assert::false( $input->isFilled() );
	Assert::false( $input->getValue() );
});
