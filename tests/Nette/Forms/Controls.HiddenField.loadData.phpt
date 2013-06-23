<?php

/**
 * Test: Nette\Forms\Controls\HiddenField.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Form;



require __DIR__ . '/../bootstrap.php';



$_SERVER['REQUEST_METHOD'] = 'POST';

$_POST = array(
	'text' => "  a\r b \n c ",
	'malformed' => array(NULL),
);


test(function() {
	$form = new Form;
	$input = $form->addHidden('text');
	Assert::same( "  a\r b \n c ", $input->getValue() );
	Assert::true( $input->isFilled() );
});



test(function() {
	$form = new Form;
	$input = $form->addText('unknown');
	Assert::same( '', $input->getValue() );
	Assert::false( $input->isFilled() );
});



test(function() { // invalid data
	$form = new Form;
	$input = $form->addHidden('malformed');
	Assert::same( '', $input->getValue() );
	Assert::false( $input->isFilled() );
});
