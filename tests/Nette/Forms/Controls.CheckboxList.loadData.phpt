<?php

/**
 * Test: Nette\Forms\Controls\CheckboxList.
 *
 * @author     David Grudl
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


test(function() use ($series) { // invalid input
	$_POST = array('list' => 'red-dwarf');

	$form = new Form;
	$input = $form->addCheckboxList('list', NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( array(), $input->getValue() );
	Assert::same( array(), $input->getSelectedItems() );
	Assert::false( $input->isFilled() );
});


test(function() use ($series) { // multiple selected items, zero item
	$_POST = array('multi' => array('red-dwarf', 'unknown', 0));

	$form = new Form;
	$input = $form->addCheckboxList('multi', NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( array('red-dwarf', 0), $input->getValue() );
	Assert::same( array('red-dwarf', 'unknown', 0), $input->getRawValue() );
	Assert::same( array('red-dwarf' => 'Red Dwarf', 0 => 'South Park'), $input->getSelectedItems() );
	Assert::true( $input->isFilled() );
});


test(function() use ($series) { // empty key
	$_POST = array('empty' => array(''));

	$form = new Form;
	$input = $form->addCheckboxList('empty', NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( array(''), $input->getValue() );
	Assert::same( array('' => 'Family Guy'), $input->getSelectedItems() );
	Assert::true( $input->isFilled() );
});


test(function() use ($series) { // missing key
	$form = new Form;
	$input = $form->addCheckboxList('missing', NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( array(), $input->getValue() );
	Assert::same( array(), $input->getSelectedItems() );
	Assert::false( $input->isFilled() );
});


test(function() use ($series) { // disabled key
	$_POST = array('disabled' => 'red-dwarf');

	$form = new Form;
	$input = $form->addCheckboxList('disabled', NULL, $series)
		->setDisabled();

	Assert::true( $form->isValid() );
	Assert::same( array(), $input->getValue() );
});


test(function() use ($series) { // malformed data
	$_POST = array('malformed' => array(array(NULL)));

	$form = new Form;
	$input = $form->addCheckboxList('malformed', NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( array(), $input->getValue() );
	Assert::same( array(), $input->getSelectedItems() );
	Assert::false( $input->isFilled() );
});


test(function() use ($series) { // validateLength
	$_POST = array('multi' => array('red-dwarf', 'unknown', 0));

	$form = new Form;
	$input = $form->addCheckboxList('multi', NULL, $series);

	Assert::true( $input::validateLength($input, 2) );
	Assert::false( $input::validateLength($input, 3) );
	Assert::false( $input::validateLength($input, array(3, )) );
	Assert::true( $input::validateLength($input, array(0, 3)) );
});


test(function() use ($series) { // validateEqual
	$_POST = array('multi' => array('red-dwarf', 'unknown', 0));

	$form = new Form;
	$input = $form->addCheckboxList('multi', NULL, $series);

	Assert::true( $input::validateEqual($input, array('red-dwarf', 0)) );
	Assert::false( $input::validateEqual($input, 'unknown') );
	Assert::false( $input::validateEqual($input, array('unknown')) );
	Assert::false( $input::validateEqual($input, array(0)) );
});


test(function() use ($series) { // setValue() and invalid argument
	$form = new Form;
	$input = $form->addCheckboxList('list', NULL, $series);
	$input->setValue(NULL);

	Assert::exception(function() use ($input) {
		$input->setValue('unknown');
	}, 'Nette\InvalidArgumentException', "Values 'unknown' are out of allowed range in field 'list'.");
});


test(function() { // object as value
	$form = new Form;
	$input = $form->addCheckboxList('list', NULL, array('2013-07-05 00:00:00' => 1))
		->setValue(array(new DateTime('2013-07-05')));

	Assert::same( array('2013-07-05 00:00:00'), $input->getValue() );
});


test(function() { // object as item
	$form = new Form;
	$input = $form->addCheckboxList('list')
		->setItems(array(new DateTime('2013-07-05')), FALSE)
		->setValue('2013-07-05 00:00:00');

	Assert::equal( array('2013-07-05 00:00:00' => new DateTime('2013-07-05')), $input->getSelectedItems() );
});


test(function() use ($series) { // disabled one
	$_POST = array('list' => array('red-dwarf', 0));

	$form = new Form;
	$input = $form->addCheckboxList('list', NULL, $series)
		->setDisabled(array('red-dwarf'));

	Assert::same( array(0), $input->getValue() );

	unset($form['list']);
	$input = new Nette\Forms\Controls\CheckboxList(NULL, $series);
	$input->setDisabled(array('red-dwarf'));
	$form['list'] = $input;

	Assert::same( array(0), $input->getValue() );
});
