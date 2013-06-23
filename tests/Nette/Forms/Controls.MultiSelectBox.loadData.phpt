<?php

/**
 * Test: Nette\Forms\Controls\MultiSelectBox.
 *
 * @author     Martin Major
 * @package    Nette\Forms
 */

use Nette\Forms\Form;



require __DIR__ . '/../bootstrap.php';



$_SERVER['REQUEST_METHOD'] = 'POST';

$_POST = array(
	'string1' => 'red-dwarf',
	'string2' => 'days-of-our-lives',
	'zero' => 0,
	'empty' => '',
	'malformed' => array(array(NULL)),
);

$series = array(
	'red-dwarf' => 'Red Dwarf',
	'the-simpsons' => 'The Simpsons',
	0 => 'South Park',
	'' => 'Family Guy',
);


test(function() use ($series) {
	$form = new Form;
	$input = $form->addMultiSelect('string1', NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( array('red-dwarf'), $input->getValue() );
	Assert::true( $input->isFilled() );
});



test(function() use ($series) { // invalid input
	$form = new Form;
	$input = $form->addMultiSelect('string2', NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( array(), $input->getValue() );
	Assert::false( $input->isFilled() );
});



test(function() use ($series) {
	$form = new Form;
	$input = $form->addMultiSelect('zero', NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( array(0), $input->getValue() );
	Assert::same( array(0), $input->getRawValue() );
	Assert::true( $input->isFilled() );
});



test(function() use ($series) { // empty key
	$form = new Form;
	$input = $form->addMultiSelect('empty', NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( array(''), $input->getValue() );
	Assert::true( $input->isFilled() );
});



test(function() use ($series) { // missing key
	$form = new Form;
	$input = $form->addMultiSelect('missing', NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( array(), $input->getValue() );
	Assert::false( $input->isFilled() );
});



test(function() use ($series) { // malformed data
	$form = new Form;
	$input = $form->addMultiSelect('malformed', NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( array(), $input->getValue() );
	Assert::false( $input->isFilled() );
});
