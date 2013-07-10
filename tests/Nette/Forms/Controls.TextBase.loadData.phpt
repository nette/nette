<?php

/**
 * Test: Nette\Forms\Controls\TextInput.
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


test(function() { // trim & new lines
	$_POST = array('text' => "  a\r b \n c ");

	$form = new Form;
	$input = $form->addText('text');

	Assert::same( 'a  b   c', $input->getValue() );
	Assert::true( $input->isFilled() );
});


test(function() { // trim & new lines in textarea
	$_POST = array('text' => "  a\r b \n c ");

	$form = new Form;
	$input = $form->addTextArea('text');

	Assert::same( "  a\n b \n c ", $input->getValue() );
});


test(function() { // empty value
	$_POST = array('url' => 'nette.org');

	$form = new Form;
	$input = $form->addText('url')
		->setEmptyValue('nette.org');

	Assert::same( '', $input->getValue() );
});


test(function() { // invalid UTF
	$_POST = array('invalidutf' => "invalid\xAA\xAA\xAAutf");

	$form = new Form;
	$input = $form->addText('invalidutf');
	Assert::same( '', $input->getValue() );
});


test(function() { // missing data
	$form = new Form;
	$input = $form->addText('unknown');

	Assert::same( '', $input->getValue() );
	Assert::false( $input->isFilled() );
});


test(function() { // malformed data
	$_POST = array('malformed' => array(NULL));

	$form = new Form;
	$input = $form->addText('malformed');

	Assert::same( '', $input->getValue() );
	Assert::false( $input->isFilled() );
});


test(function() { // setValue() and invalid argument
	$_POST = array('text' => "  a\r b \n c ");

	$form = new Form;
	$input = $form->addText('text');
	$input->setValue(NULL);

	Assert::exception(function() use ($input) {
		$input->setValue(array());
	}, 'Nette\InvalidArgumentException', "Value must be scalar or NULL, array given.");
});


test(function() { // float
	$_POST = array('number' => ' 10,5 ');

	$form = new Form;
	$input = $form->addText('number')
		->addRule($form::FLOAT);

	Assert::same( '10.5', $input->getValue() );
});


test(function() { // non float
	$_POST = array('number' => ' 10,5 ');

	$form = new Form;
	$input = $form->addText('number')
		->addRule(~$form::FLOAT);

	Assert::same( '10,5', $input->getValue() );
});


test(function() { // max length
	$_POST = array('long' => ' žluťoučký');

	$form = new Form;
	$input = $form->addText('long')
		->addRule($form::MAX_LENGTH, NULL, 5);

	Assert::same( 'žluťo', $input->getValue() );
});


test(function() { // max length
	$_POST = array('long' => ' žluťoučký');

	$form = new Form;
	$input = $form->addTextArea('long')
		->addRule($form::MAX_LENGTH, NULL, 5);

	Assert::same( ' žluť', $input->getValue() );
});


test(function() { // URL
	$_POST = array('url' => 'nette.org');

	$form = new Form;
	$input = $form->addText('url')
		->addRule($form::URL);

	Assert::same( 'http://nette.org', $input->getValue() );
});


test(function() { // object
	$form = new Form;
	$input = $form->addText('text')
		->setValue(new Nette\DateTime('2013-07-05'));

	Assert::same( '2013-07-05 00:00:00', $input->getValue() );
});
