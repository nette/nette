<?php

/**
 * Test: Nette\Forms\Controls\MultiChoiceControl.
 *
 * @author     David Grudl
 */

use Nette\Forms\Form,
	Nette\Forms\Validator,
	Nette\DateTime,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class MultiChoiceControl extends Nette\Forms\Controls\MultiChoiceControl
{}


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
	$_POST = array('select' => 'red-dwarf');

	$form = new Form;
	$input = $form['select'] = new MultiChoiceControl(NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( array(), $input->getValue() );
	Assert::same( array(), $input->getSelectedItems() );
	Assert::false( $input->isFilled() );
});


test(function() use ($series) { // multiple selected items, zero item
	$_POST = array('multi' => array('red-dwarf', 'unknown', 0));

	$form = new Form;
	$input = $form['multi'] = new MultiChoiceControl(NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( array('red-dwarf', 0), $input->getValue() );
	Assert::same( array('red-dwarf', 'unknown', 0), $input->getRawValue() );
	Assert::same( array('red-dwarf' => 'Red Dwarf', 0 => 'South Park'), $input->getSelectedItems() );
	Assert::true( $input->isFilled() );
});


test(function() use ($series) { // empty key
	$_POST = array('empty' => array(''));

	$form = new Form;
	$input = $form['empty'] = new MultiChoiceControl(NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( array(''), $input->getValue() );
	Assert::same( array('' => 'Family Guy'), $input->getSelectedItems() );
	Assert::true( $input->isFilled() );
});


test(function() use ($series) { // missing key
	$form = new Form;
	$input = $form['missing'] = new MultiChoiceControl(NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( array(), $input->getValue() );
	Assert::same( array(), $input->getSelectedItems() );
	Assert::false( $input->isFilled() );
});


test(function() use ($series) { // disabled key
	$_POST = array('disabled' => 'red-dwarf');

	$form = new Form;
	$input = $form['disabled'] = new MultiChoiceControl(NULL, $series);
	$input->setDisabled();

	Assert::true( $form->isValid() );
	Assert::same( array(), $input->getValue() );
});


test(function() use ($series) { // malformed data
	$_POST = array('malformed' => array(array(NULL)));

	$form = new Form;
	$input = $form['malformed'] = new MultiChoiceControl(NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( array(), $input->getValue() );
	Assert::same( array(), $input->getSelectedItems() );
	Assert::false( $input->isFilled() );
});


test(function() use ($series) { // setItems without keys
	$_POST = array('multi' => array('red-dwarf'));

	$form = new Form;
	$input = $form['multi'] = new MultiChoiceControl;
	$input->setItems(array_keys($series), FALSE);
	Assert::same( array(
		'red-dwarf' => 'red-dwarf',
		'the-simpsons' => 'the-simpsons',
		0 => 0,
		'' => '',
	), $input->getItems() );

	Assert::true( $form->isValid() );
	Assert::same( array('red-dwarf'), $input->getValue() );
	Assert::same( array('red-dwarf' => 'red-dwarf'), $input->getSelectedItems() );
	Assert::true( $input->isFilled() );
});


test(function() use ($series) { // validateLength
	$_POST = array('multi' => array('red-dwarf', 'unknown', 0));

	$form = new Form;
	$input = $form['multi'] = new MultiChoiceControl(NULL, $series);

	Assert::true( Validator::validateLength($input, 2) );
	Assert::false( Validator::validateLength($input, 3) );
	Assert::false( Validator::validateLength($input, array(3, )) );
	Assert::true( Validator::validateLength($input, array(0, 3)) );
});


test(function() use ($series) { // validateEqual
	$_POST = array('multi' => array('red-dwarf', 'unknown', 0));

	$form = new Form;
	$input = $form['multi'] = new MultiChoiceControl(NULL, $series);

	Assert::true( Validator::validateEqual($input, array('red-dwarf', 0)) );
	Assert::false( Validator::validateEqual($input, 'unknown') );
	Assert::false( Validator::validateEqual($input, array('unknown')) );
	Assert::false( Validator::validateEqual($input, array(0)) );
});


test(function() use ($series) { // setValue() and invalid argument
	$form = new Form;
	$input = $form['select'] = new MultiChoiceControl(NULL, $series);
	$input->setValue(NULL);

	Assert::exception(function() use ($input) {
		$input->setValue('unknown');
	}, 'Nette\InvalidArgumentException', "Value 'unknown' are out of allowed range ['Red Dwarf', 'The Simpsons', 'South Park', 'Family Guy'] in field 'select'.");

	Assert::exception(function() use ($input) {
		$input->setValue(new stdClass);
	}, 'Nette\InvalidArgumentException', "Value must be array or NULL, object given in field 'select'.");

	Assert::exception(function() use ($input) {
		$input->setValue(array(new stdClass));
	}, 'Nette\InvalidArgumentException', "Values must be scalar, object given in field 'select'.");
});


test(function() { // object as value
	$form = new Form;
	$input = $form['select'] = new MultiChoiceControl(NULL, array('2013-07-05 00:00:00' => 1));
	$input->setValue(array(new DateTime('2013-07-05')));

	Assert::same( array('2013-07-05 00:00:00'), $input->getValue() );
});


test(function() use ($series) { // disabled one
	$_POST = array('select' => array('red-dwarf', 0));

	$form = new Form;
	$input = $form['select'] = new MultiChoiceControl(NULL, $series);
	$input->setDisabled(array('red-dwarf'));

	Assert::same( array(0), $input->getValue() );

	unset($form['select']);
	$input = new Nette\Forms\Controls\MultiSelectBox(NULL, $series);
	$input->setDisabled(array('red-dwarf'));
	$form['select'] = $input;

	Assert::same( array(0), $input->getValue() );
});
