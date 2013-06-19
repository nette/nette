<?php

/**
 * Test: Nette\Forms\Controls\TextInput.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Form;



require __DIR__ . '/../bootstrap.php';



$_SERVER['REQUEST_METHOD'] = 'POST';

$_POST = array(
	'text' => "  a\r b \n c ",
	'number' => ' 10,5 ',
	'long' => ' žluťoučký',
);


test(function() { // trim & new lines
	$form = new Form();
	$form->addText('text');

	Assert::same( 'a  b   c', $form['text']->getValue() );
});



test(function() { // trim & new lines
	$form = new Form();
	$form->addTextArea('text');

	Assert::same( "  a\n b \n c ", $form['text']->getValue() );
});



test(function() { // float
	$form = new Form();
	$form->addText('number')
		->addRule($form::FLOAT);

	Assert::same( '10.5', $form['number']->getValue() );
});



test(function() { // non float
	$form = new Form();
	$form->addText('number')
		->addRule(~$form::FLOAT);

	Assert::same( '10,5', $form['number']->getValue() );
});



test(function() { // max length
	$form = new Form();
	$form->addText('long')
		->addRule($form::MAX_LENGTH, NULL, 5);

	Assert::same( 'žluť', $form['long']->getValue() );
});



test(function() { // max length
	$form = new Form();
	$form->addTextArea('long')
		->addRule($form::MAX_LENGTH, NULL, 5);

	Assert::same( ' žluť', $form['long']->getValue() );
});
