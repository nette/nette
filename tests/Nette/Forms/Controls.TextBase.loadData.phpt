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
	'url' => 'nette.org',
	'malformed' => array(NULL),
	'invalidutf' => "invalid\xAA\xAA\xAAutf",
);


test(function() { // trim & new lines
	$form = new Form;
	$input = $form->addText('text');

	Assert::same( 'a  b   c', $input->getValue() );
	Assert::true( $input->isFilled() );
});



test(function() { // trim & new lines in textarea
	$form = new Form;
	$input = $form->addTextArea('text');

	Assert::same( "  a\n b \n c ", $input->getValue() );
});



test(function() { // empty value
	$form = new Form;
	$input = $form->addText('url')
		->setEmptyValue('nette.org');

	Assert::same( '', $input->getValue() );
});



test(function() { // invalid UTF
	$form = new Form;
	$input = $form->addText('invalidutf');
	Assert::same( 'invalidutf', $input->getValue() );
});



test(function() { // missing data
	$form = new Form;
	$input = $form->addText('unknown');

	Assert::same( '', $input->getValue() );
	Assert::false( $input->isFilled() );
});



test(function() { // malformed data
	$form = new Form;
	$input = $form->addText('malformed');

	Assert::same( '', $input->getValue() );
	Assert::false( $input->isFilled() );
});



test(function() { // float
	$form = new Form;
	$input = $form->addText('number')
		->addRule($form::FLOAT);

	Assert::same( '10.5', $input->getValue() );
});



test(function() { // non float
	$form = new Form;
	$input = $form->addText('number')
		->addRule(~$form::FLOAT);

	Assert::same( '10,5', $input->getValue() );
});



test(function() { // max length
	$form = new Form;
	$input = $form->addText('long')
		->addRule($form::MAX_LENGTH, NULL, 5);

	Assert::same( 'žluť', $input->getValue() );
});



test(function() { // max length
	$form = new Form;
	$input = $form->addTextArea('long')
		->addRule($form::MAX_LENGTH, NULL, 5);

	Assert::same( ' žluť', $input->getValue() );
});



test(function() { // URL
	$form = new Form;
	$input = $form->addText('url')
		->addRule($form::URL);

	Assert::same( 'http://nette.org', $input->getValue() );
});
