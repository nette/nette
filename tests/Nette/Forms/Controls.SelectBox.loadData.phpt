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
	Assert::same( 'Red Dwarf', $input->getSelectedItem() );
	Assert::true( $input->isFilled() );
});



test(function() use ($series) { // Select with prompt
	$form = new Form;
	$input = $form->addSelect('string1', NULL, $series)->setPrompt('Select series');

	Assert::true( $form->isValid() );
	Assert::same( 'red-dwarf', $input->getValue() );
	Assert::same( 'Red Dwarf', $input->getSelectedItem() );
	Assert::true( $input->isFilled() );
});



test(function() use ($series) { // Select with optgroups
	$form = new Form;
	$input = $form->addSelect('string1', NULL, array(
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
	$form = new Form;
	$input = $form->addSelect('string2', NULL, $series);

	Assert::false( $form->isValid() );
	Assert::null( $input->getValue() );
	Assert::null( $input->getSelectedItem() );
	Assert::false( $input->isFilled() );
});



test(function() use ($series) { // Select with prompt and invalid input
	$form = new Form;
	$input = $form->addSelect('string2', NULL, $series)->setPrompt('Select series');

	Assert::true( $form->isValid() );
	Assert::null( $input->getValue() );
	Assert::null( $input->getSelectedItem() );
	Assert::false( $input->isFilled() );
});



test(function() use ($series) { // Indexed arrays
	$form = new Form;
	$input = $form->addSelect('zero', NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( 0, $input->getValue() );
	Assert::same( 0, $input->getRawValue() );
	Assert::same( 'South Park', $input->getSelectedItem() );
	Assert::true( $input->isFilled() );
});



test(function() use ($series) { // empty key
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
	$form = new Form;
	$input = $form->addSelect('malformed', NULL, $series);

	Assert::false( $form->isValid() );
	Assert::null( $input->getValue() );
	Assert::null( $input->getSelectedItem() );
	Assert::false( $input->isFilled() );
});



test(function() use ($series) { // setItems without keys
	$form = new Form;
	$input = $form->addSelect('string1')->setItems(array_keys($series), FALSE);

	Assert::true( $form->isValid() );
	Assert::same( 'red-dwarf', $input->getValue() );
	Assert::same( 'red-dwarf', $input->getSelectedItem() );
	Assert::true( $input->isFilled() );
});



test(function() { // setItems without keys with optgroups
	$form = new Form;
	$input = $form->addSelect('string1')->setItems(array(
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
		$form->addSelect('string1', NULL, array(
			'usa' => array('the-simpsons' => 'The Simpsons'),
			'uk' => array('the-simpsons' => 'Red Dwarf'),
		));
	}, 'Nette\InvalidArgumentException', "Items contain duplication for key 'the-simpsons'.");

	Assert::exception(function() use ($form) {
		$form->addSelect('string1')->setItems(array(
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
