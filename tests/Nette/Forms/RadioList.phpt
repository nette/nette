<?php

/**
 * Test: Nette\Forms\Controls\RadioList.
 *
 * @author     David Grudl
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


// Radio list

$form = new Form();
$form->addRadioList('series0', NULL, $series);

Assert::true( (bool) $form->isSubmitted() );
Assert::true( $form->isValid() );
Assert::same( array(
	'series0' => 'red-dwarf',
), (array) $form->getValues() );



// Radio list with invalid input

$form = new Form();
$form->addRadioList('series1', NULL, $series);

Assert::true( (bool) $form->isSubmitted() );
Assert::true( $form->isValid() );
Assert::same( array(
	'series1' => NULL,
), (array) $form->getValues() );


// Indexed arrays

$form = new Form();
$form->addRadioList('series2', NULL, $series);

Assert::true( (bool) $form->isSubmitted() );
Assert::true( $form->isValid() );
Assert::same( array(
	'series2' => 0,
), (array) $form->getValues() );
Assert::same( 0, $form['series2']->getRawValue() );
