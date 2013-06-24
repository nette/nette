<?php

/**
 * Test: Nette\Forms\Controls\Checkbox.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Form;



require __DIR__ . '/../bootstrap.php';



$_SERVER['REQUEST_METHOD'] = 'POST';

$_POST = array(
	'off' => '',
	'on' => 1,
	'malformed' => array(NULL),
);


test(function() {
	$form = new Form;
	$input = $form->addCheckbox('off');

	Assert::false( $input->getValue() );
	Assert::false( $input->isFilled() );

	$input = $form->addCheckbox('on');

	Assert::true( $input->getValue() );
	Assert::true( $input->isFilled() );
});



test(function() { // malformed data
	$form = new Form;
	$input = $form->addCheckbox('malformed');

	Assert::false( $input->getValue() );
	Assert::false( $input->isFilled() );
});



test(function() { // setValue() and invalid argument
	$form = new Form;
	$input = $form->addCheckbox('checkbox');
	$input->setValue(NULL);

	Assert::exception(function() use ($input) {
		$input->setValue(array());
	}, 'Nette\InvalidArgumentException', "Value must be scalar or NULL, array given.");
});
