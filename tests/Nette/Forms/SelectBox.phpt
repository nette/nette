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
	'series0' => 'red-dwarf',
	'series1' => 'days-of-our-lives',
	'series2' => 0,
);

$series = array(
	'red-dwarf' => 'Red Dwarf',
	'the-simpsons' => 'The Simpsons',
	0 => 'South Park',
);


// Select

$form = new Form();
$form->addSelect('series0', NULL, $series);

Assert::true( (bool) $form->isSubmitted() );
Assert::true( $form->isValid() );
Assert::same( array(
	'series0' => 'red-dwarf',
), (array) $form->getValues() );


// Select with prompt

$form = new Form();
$form->addSelect('series0', NULL, $series)->setPrompt('Select series');

Assert::true( (bool) $form->isSubmitted() );
Assert::true( $form->isValid() );
Assert::same( array(
	'series0' => 'red-dwarf',
), (array) $form->getValues() );


// MultiSelect

$form = new Form();
$form->addMultiSelect('series0', NULL, $series);

Assert::true( (bool) $form->isSubmitted() );
Assert::true( $form->isValid() );
Assert::same( array(
	'series0' => array('red-dwarf'),
), (array) $form->getValues() );



// Select with invalid input

$form = new Form();
$form->addSelect('series1', NULL, $series);

Assert::true( (bool) $form->isSubmitted() );
Assert::false( $form->isValid() );
Assert::same( array(
	'series1' => NULL,
), (array) $form->getValues() );


// Select with prompt and invalid input

$form = new Form();
$form->addSelect('series1', NULL, $series)->setPrompt('Select series');

Assert::true( (bool) $form->isSubmitted() );
Assert::true( $form->isValid() );
Assert::same( array(
	'series1' => NULL,
), (array) $form->getValues() );


// MultiSelect with invalid input

$form = new Form();
$form->addMultiSelect('series1', NULL, $series);

Assert::true( (bool) $form->isSubmitted() );
Assert::true( $form->isValid() );
Assert::same( array(
	'series1' => array(),
), (array) $form->getValues() );


// Indexed arrays

$form = new Form();
$form->addSelect('series2', NULL, $series);

Assert::true( (bool) $form->isSubmitted() );
Assert::true( $form->isValid() );
Assert::same( array(
	'series2' => 0,
), (array) $form->getValues() );
Assert::same( 0, $form['series2']->getRawValue() );


$form = new Form();
$form->addMultiSelect('series2', NULL, $series);

Assert::true( (bool) $form->isSubmitted() );
Assert::true( $form->isValid() );
Assert::same( array(
	'series2' => array(0),
), (array) $form->getValues() );
Assert::same( array(0), $form['series2']->getRawValue() );
