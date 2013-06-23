<?php

/**
 * Test: Nette\Forms\Controls\ImageButton.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Form;



require __DIR__ . '/../bootstrap.php';



$_SERVER['REQUEST_METHOD'] = 'POST';

$_POST = array(
	'image' => array(1, 2),
	'container' => array(
		'image' => array(3, 4),
	),
);


test(function() {
	$form = new Form;
	$input = $form->addImage('image');
	Assert::true( $input->isFilled() );
	Assert::same( array('1', '2'), $input->getValue() );

	$input = $form->addContainer('container')->addImage('image');
	Assert::same( array('3', '4'), $form['container']['image']->getValue() );
});



test(function() { // missing data
	$form = new Form;
	$input = $form->addImage('unknown');
	Assert::false( $input->isFilled() );
	Assert::false( $input->getValue() );
});
