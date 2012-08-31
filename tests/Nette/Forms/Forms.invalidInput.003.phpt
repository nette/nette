<?php

/**
 * Test: Nette\Forms invalid input.
 *
 * @author     Martin Major
 * @package    Nette\Forms
 * @subpackage UnitTests
 */

use Nette\Forms\Form,
	Nette\ArrayHash;



require __DIR__ . '/../bootstrap.php';



$_SERVER['REQUEST_METHOD'] = 'POST';

$_POST = array(
	'series' => 'days-of-our-lives',
	'ok' => 'Send',
);

$series = array(
	'red-dwarf' => 'Red Dwarf',
	'the-simpsons' => 'The Simpsons',
);


// Select

$form = new Form();
$form->addSelect('series', 'Series:', $series);
$form->addSubmit('ok', 'Send');

Assert::true( (bool) $form->isSubmitted() );
Assert::false( $form->isValid() );
Assert::equal( ArrayHash::from(array(
	'series' => NULL,
), FALSE), $form->getValues() );


// Select with prompt

$form = new Form();
$form->addSelect('series', 'Series:', $series)->setPrompt('Select series');
$form->addSubmit('ok', 'Send');

Assert::true( (bool) $form->isSubmitted() );
Assert::true( $form->isValid() );
Assert::equal( ArrayHash::from(array(
	'series' => NULL,
), FALSE), $form->getValues() );


// MultiSelect

$form = new Form();
$form->addMultiSelect('series', 'Series:', $series);
$form->addSubmit('ok', 'Send');

Assert::true( (bool) $form->isSubmitted() );
Assert::true( $form->isValid() );
Assert::equal( ArrayHash::from(array(
	'series' => array(),
), FALSE), $form->getValues() );
