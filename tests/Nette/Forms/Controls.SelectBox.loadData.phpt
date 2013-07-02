<?php

/**
 * Test: Nette\Forms\Controls\SelectBox.
 *
 * @author     Martin Major
 * @package    Nette\Forms
 */

use Nette\Forms\Form;


require __DIR__ . '/../bootstrap.php';


before(function() {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = $_FILES = array();
});


$series = array(
	'red-dwarf' => 'Red Dwarf',
	'the-simpsons' => 'The Simpsons',
	0 => 'South Park',
	'' => 'Family Guy',
);


test(function() use ($series) { // Select
	$_POST = array('select' => 'red-dwarf');

	$form = new Form;
	$input = $form->addSelect('select', NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( 'red-dwarf', $input->getValue() );
	Assert::same( 'Red Dwarf', $input->getSelectedItem() );
	Assert::true( $input->isFilled() );
});


test(function() use ($series) { // Select with prompt
	$_POST = array('select' => 'red-dwarf');

	$form = new Form;
	$input = $form->addSelect('select', NULL, $series)->setPrompt('Select series');

	Assert::true( $form->isValid() );
	Assert::same( 'red-dwarf', $input->getValue() );
	Assert::same( 'Red Dwarf', $input->getSelectedItem() );
	Assert::true( $input->isFilled() );
});


test(function() use ($series) { // Select with optgroups
	$_POST = array('select' => 'red-dwarf');

	$form = new Form;
	$input = $form->addSelect('select', NULL, array(
		'usa' => array(
			'the-simpsons' => 'The Simpsons',
			0 => 'South Park',
		),
		'uk' => array(
			'red-dwarf' => 'Red Dwarf',
		),
	));

	Assert::true( $form->isValid() );
	Assert::same( 'red-dwarf', $input->getValue() );
	Assert::same( 'Red Dwarf', $input->getSelectedItem() );
	Assert::true( $input->isFilled() );
});


test(function() use ($series) { // Select with invalid input
	$_POST = array('select' => 'days-of-our-lives');

	$form = new Form;
	$input = $form->addSelect('select', NULL, $series);

	Assert::false( $form->isValid() );
	Assert::null( $input->getValue() );
	Assert::null( $input->getSelectedItem() );
	Assert::false( $input->isFilled() );
});


test(function() use ($series) { // Select with prompt and invalid input
	$form = new Form;
	$input = $form->addSelect('select', NULL, $series)->setPrompt('Select series');

	Assert::true( $form->isValid() );
	Assert::null( $input->getValue() );
	Assert::null( $input->getSelectedItem() );
	Assert::false( $input->isFilled() );
});


test(function() use ($series) { // Indexed arrays
	$_POST = array('zero' => 0);

	$form = new Form;
	$input = $form->addSelect('zero', NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( 0, $input->getValue() );
	Assert::same( 0, $input->getRawValue() );
	Assert::same( 'South Park', $input->getSelectedItem() );
	Assert::true( $input->isFilled() );
});


test(function() use ($series) { // empty key
	$_POST = array('empty' => '');

	$form = new Form;
	$input = $form->addSelect('empty', NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( '', $input->getValue() );
	Assert::same( 'Family Guy', $input->getSelectedItem() );
	Assert::true( $input->isFilled() );
});


test(function() use ($series) { // missing key
	$form = new Form;
	$input = $form->addSelect('missing', NULL, $series);

	Assert::false( $form->isValid() );
	Assert::null( $input->getValue() );
	Assert::null( $input->getSelectedItem() );
	Assert::false( $input->isFilled() );
});


test(function() use ($series) { // malformed data
	$_POST = array('malformed' => array(NULL));

	$form = new Form;
	$input = $form->addSelect('malformed', NULL, $series);

	Assert::false( $form->isValid() );
	Assert::null( $input->getValue() );
	Assert::null( $input->getSelectedItem() );
	Assert::false( $input->isFilled() );
});


test(function() use ($series) { // setItems without keys
	$_POST = array('select' => 'red-dwarf');

	$form = new Form;
	$input = $form->addSelect('select')->setItems(array_keys($series), FALSE);

	Assert::true( $form->isValid() );
	Assert::same( 'red-dwarf', $input->getValue() );
	Assert::same( 'red-dwarf', $input->getSelectedItem() );
	Assert::true( $input->isFilled() );
});


test(function() { // setItems without keys with optgroups
	$_POST = array('select' => 'red-dwarf');

	$form = new Form;
	$input = $form->addSelect('select')->setItems(array(
		'usa' => array('the-simpsons', 0),
		'uk' => array('red-dwarf'),
	), FALSE);

	Assert::true( $form->isValid() );
	Assert::same( 'red-dwarf', $input->getValue() );
	Assert::same( 'red-dwarf', $input->getSelectedItem() );
	Assert::true( $input->isFilled() );
});


test(function() {  // doubled item
	$form = new Form;

	Assert::exception(function() use ($form) {
		$form->addSelect('select', NULL, array(
			'usa' => array('the-simpsons' => 'The Simpsons'),
			'uk' => array('the-simpsons' => 'Red Dwarf'),
		));
	}, 'Nette\InvalidArgumentException', "Items contain duplication for key 'the-simpsons'.");

	Assert::exception(function() use ($form) {
		$form->addSelect('select')->setItems(array(
			'the-simpsons', 'the-simpsons',
		), FALSE);
	}, 'Nette\InvalidArgumentException', "Items contain duplication for key 'the-simpsons'.");
});


test(function() use ($series) { // setValue() and invalid argument
	$form = new Form;
	$input = $form->addSelect('select', NULL, $series);
	$input->setValue(NULL);

	Assert::exception(function() use ($input) {
		$input->setValue('unknown');
	}, 'Nette\InvalidArgumentException', "Value 'unknown' is out of range of current items.");
});
