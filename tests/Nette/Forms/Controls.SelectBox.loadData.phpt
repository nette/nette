<?php

/**
 * Test: Nette\Forms\Controls\SelectBox.
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
	'malformed' => array(NULL),
);

$series = array(
	'red-dwarf' => 'Red Dwarf',
	'the-simpsons' => 'The Simpsons',
	0 => 'South Park',
	'' => 'Family Guy',
);


test(function() use ($series) { // Select
	$form = new Form;
	$input = $form->addSelect('string1', NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( 'red-dwarf', $input->getValue() );
	Assert::true( $input->isFilled() );
});



test(function() use ($series) { // Select with prompt
	$form = new Form;
	$input = $form->addSelect('string1', NULL, $series)->setPrompt('Select series');

	Assert::true( $form->isValid() );
	Assert::same( 'red-dwarf', $input->getValue() );
	Assert::true( $input->isFilled() );
});



test(function() use ($series) { // Select with invalid input
	$form = new Form;
	$input = $form->addSelect('string2', NULL, $series);

	Assert::false( $form->isValid() );
	Assert::null( $input->getValue() );
	Assert::false( $input->isFilled() );
});



test(function() use ($series) { // Select with prompt and invalid input
	$form = new Form;
	$input = $form->addSelect('string2', NULL, $series)->setPrompt('Select series');

	Assert::true( $form->isValid() );
	Assert::null( $input->getValue() );
	Assert::false( $input->isFilled() );
});



test(function() use ($series) { // Indexed arrays
	$form = new Form;
	$input = $form->addSelect('zero', NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( 0, $input->getValue() );
	Assert::same( 0, $input->getRawValue() );
	Assert::true( $input->isFilled() );
});



test(function() use ($series) { // empty key
	$form = new Form;
	$input = $form->addSelect('empty', NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( '', $input->getValue() );
	Assert::true( $input->isFilled() );
});



test(function() use ($series) { // missing key
	$form = new Form;
	$input = $form->addSelect('missing', NULL, $series);

	Assert::false( $form->isValid() );
	Assert::null( $input->getValue() );
	Assert::false( $input->isFilled() );
});



test(function() use ($series) { // malformed data
	$form = new Form;
	$input = $form->addSelect('malformed', NULL, $series);

	Assert::false( $form->isValid() );
	Assert::null( $input->getValue() );
	Assert::false( $input->isFilled() );
});
