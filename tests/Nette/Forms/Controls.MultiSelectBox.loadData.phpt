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
	'series0' => 'red-dwarf',
	'series1' => 'days-of-our-lives',
	'series2' => 0,
);

$series = array(
	'red-dwarf' => 'Red Dwarf',
	'the-simpsons' => 'The Simpsons',
	0 => 'South Park',
);


test(function() use ($series) {

	$form = new Form();
	$form->addMultiSelect('series0', NULL, $series);

	Assert::true( (bool) $form->isSubmitted() );
	Assert::true( $form->isValid() );
	Assert::same( array(
		'series0' => array('red-dwarf'),
	), (array) $form->getValues() );
});



test(function() use ($series) { // invalid input

	$form = new Form();
	$form->addMultiSelect('series1', NULL, $series);

	Assert::true( (bool) $form->isSubmitted() );
	Assert::true( $form->isValid() );
	Assert::same( array(
		'series1' => array(),
	), (array) $form->getValues() );
});



test(function() use ($series) {
	$form = new Form();
	$form->addMultiSelect('series2', NULL, $series);

	Assert::true( (bool) $form->isSubmitted() );
	Assert::true( $form->isValid() );
	Assert::same( array(
		'series2' => array(0),
	), (array) $form->getValues() );
	Assert::same( array(0), $form['series2']->getRawValue() );
});
