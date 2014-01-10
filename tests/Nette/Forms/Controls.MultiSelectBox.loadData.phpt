<?php

/**
 * Test: Nette\Forms\Controls\MultiSelectBox.
 *
 * @author     Martin Major
 */

use Nette\Forms\Form,
	Nette\DateTime,
	Tester\Assert;


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


test(function() use ($series) { // Select with optgroups
	$_POST = array('multi' => array('red-dwarf'));

	$form = new Form;
	$input = $form->addMultiSelect('multi', NULL, array(
		'usa' => array(
			'the-simpsons' => 'The Simpsons',
			0 => 'South Park',
		),
		'uk' => array(
			'red-dwarf' => 'Red Dwarf',
		),
	));

	Assert::true( $form->isValid() );
	Assert::same( array('red-dwarf'), $input->getValue() );
	Assert::same( array('red-dwarf' => 'Red Dwarf'), $input->getSelectedItems() );
	Assert::true( $input->isFilled() );
});


test(function() use ($series) { // invalid input
	$_POST = array('select' => 'red-dwarf');

	$form = new Form;
	$input = $form->addMultiSelect('select', NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( array(), $input->getValue() );
	Assert::same( array(), $input->getSelectedItems() );
	Assert::false( $input->isFilled() );
});


test(function() use ($series) { // multiple selected items, zero item
	$_POST = array('multi' => array('red-dwarf', 'unknown', 0));

	$form = new Form;
	$input = $form->addMultiSelect('multi', NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( array('red-dwarf', 0), $input->getValue() );
	Assert::same( array('red-dwarf', 'unknown', 0), $input->getRawValue() );
	Assert::same( array('red-dwarf' => 'Red Dwarf', 0 => 'South Park'), $input->getSelectedItems() );
	Assert::true( $input->isFilled() );
});


test(function() use ($series) { // empty key
	$_POST = array('empty' => array(''));

	$form = new Form;
	$input = $form->addMultiSelect('empty', NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( array(''), $input->getValue() );
	Assert::same( array('' => 'Family Guy'), $input->getSelectedItems() );
	Assert::true( $input->isFilled() );
});


test(function() use ($series) { // missing key
	$form = new Form;
	$input = $form->addMultiSelect('missing', NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( array(), $input->getValue() );
	Assert::same( array(), $input->getSelectedItems() );
	Assert::false( $input->isFilled() );
});


test(function() use ($series) { // disabled key
	$_POST = array('disabled' => 'red-dwarf');

	$form = new Form;
	$input = $form->addMultiSelect('disabled', NULL, $series)
		->setDisabled();

	Assert::true( $form->isValid() );
	Assert::same( array(), $input->getValue() );
});


test(function() use ($series) { // malformed data
	$_POST = array('malformed' => array(array(NULL)));

	$form = new Form;
	$input = $form->addMultiSelect('malformed', NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( array(), $input->getValue() );
	Assert::same( array(), $input->getSelectedItems() );
	Assert::false( $input->isFilled() );
});


test(function() use ($series) { // validateLength
	$_POST = array('multi' => array('red-dwarf', 'unknown', 0));

	$form = new Form;
	$input = $form->addMultiSelect('multi', NULL, $series);

	Assert::true( $input::validateLength($input, 2) );
	Assert::false( $input::validateLength($input, 3) );
	Assert::false( $input::validateLength($input, array(3, )) );
	Assert::true( $input::validateLength($input, array(0, 3)) );
});


test(function() use ($series) { // validateEqual
	$_POST = array('multi' => array('red-dwarf', 'unknown', 0));

	$form = new Form;
	$input = $form->addMultiSelect('multi', NULL, $series);

	Assert::true( $input::validateEqual($input, array('red-dwarf', 0)) );
	Assert::false( $input::validateEqual($input, 'unknown') );
	Assert::false( $input::validateEqual($input, array('unknown')) );
	Assert::false( $input::validateEqual($input, array(0)) );
});


test(function() use ($series) { // setItems without keys
	$_POST = array('multi' => array('red-dwarf'));

	$form = new Form;
	$input = $form->addMultiSelect('multi')->setItems(array_keys($series), FALSE);

	Assert::true( $form->isValid() );
	Assert::same( array('red-dwarf'), $input->getValue() );
	Assert::same( array('red-dwarf' => 'red-dwarf'), $input->getSelectedItems() );
	Assert::true( $input->isFilled() );
});


test(function() { // setItems without keys with optgroups
	$_POST = array('multi' => array('red-dwarf'));

	$form = new Form;
	$input = $form->addMultiSelect('multi')->setItems(array(
		'usa' => array('the-simpsons', 0),
		'uk' => array('red-dwarf'),
	), FALSE);

	Assert::true( $form->isValid() );
	Assert::same( array('red-dwarf'), $input->getValue() );
	Assert::same( array('red-dwarf' => 'red-dwarf'), $input->getSelectedItems() );
	Assert::true( $input->isFilled() );
});


test(function() use ($series) { // setValue() and invalid argument
	$form = new Form;
	$input = $form->addMultiSelect('select', NULL, $series);
	$input->setValue(NULL);

	Assert::exception(function() use ($input) {
		$input->setValue('unknown');
	}, 'Nette\InvalidArgumentException', "Values 'unknown' are out of allowed range in field 'select'.");
});


test(function() { // object as value
	$form = new Form;
	$input = $form->addMultiSelect('select', NULL, array('2013-07-05 00:00:00' => 1))
		->setValue(array(new DateTime('2013-07-05')));

	Assert::same( array('2013-07-05 00:00:00'), $input->getValue() );
});


test(function() { // object as item
	$form = new Form;
	$input = $form->addMultiSelect('select')
		->setItems(array(
			'group' => array(new DateTime('2013-07-05')),
			new DateTime('2013-07-06'),
		), FALSE)
		->setValue('2013-07-05 00:00:00');

	Assert::equal( array('2013-07-05 00:00:00' => new DateTime('2013-07-05')), $input->getSelectedItems() );
});


test(function() use ($series) { // disabled one
	$_POST = array('select' => array('red-dwarf', 0));

	$form = new Form;
	$input = $form->addMultiSelect('select', NULL, $series)
		->setDisabled(array('red-dwarf'));

	Assert::same( array(0), $input->getValue() );

	unset($form['select']);
	$input = new Nette\Forms\Controls\MultiSelectBox(NULL, $series);
	$input->setDisabled(array('red-dwarf'));
	$form['select'] = $input;

	Assert::same( array(0), $input->getValue() );
});
