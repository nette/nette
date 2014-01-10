<?php

/**
 * Test: Nette\Forms\Controls\ChoiceControl.
 *
 * @author     David Grudl
 */

use Nette\Forms\Form,
	Nette\DateTime,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class ChoiceControl extends Nette\Forms\Controls\ChoiceControl
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


test(function() use ($series) { // Select
	$_POST = array('select' => 'red-dwarf');

	$form = new Form;
	$input = $form['select'] = new ChoiceControl(NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( 'red-dwarf', $input->getValue() );
	Assert::same( 'Red Dwarf', $input->getSelectedItem() );
	Assert::true( $input->isFilled() );
});


test(function() use ($series) { // Select with invalid input
	$_POST = array('select' => 'days-of-our-lives');

	$form = new Form;
	$input = $form['select'] = new ChoiceControl(NULL, $series);

	Assert::true( $form->isValid() );
	Assert::null( $input->getValue() );
	Assert::null( $input->getSelectedItem() );
	Assert::false( $input->isFilled() );
});


test(function() use ($series) { // Indexed arrays
	$_POST = array('zero' => 0);

	$form = new Form;
	$input = $form['zero'] = new ChoiceControl(NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( 0, $input->getValue() );
	Assert::same( 0, $input->getRawValue() );
	Assert::same( 'South Park', $input->getSelectedItem() );
	Assert::true( $input->isFilled() );
});


test(function() use ($series) { // empty key
	$_POST = array('empty' => '');

	$form = new Form;
	$input = $form['empty'] = new ChoiceControl(NULL, $series);

	Assert::true( $form->isValid() );
	Assert::same( '', $input->getValue() );
	Assert::same( 'Family Guy', $input->getSelectedItem() );
	Assert::true( $input->isFilled() );
});


test(function() use ($series) { // missing key
	$form = new Form;
	$input = $form['missing'] = new ChoiceControl(NULL, $series);

	Assert::true( $form->isValid() );
	Assert::null( $input->getValue() );
	Assert::null( $input->getSelectedItem() );
	Assert::false( $input->isFilled() );
});


test(function() use ($series) { // disabled key
	$_POST = array('disabled' => 'red-dwarf');

	$form = new Form;
	$input = $form['disabled'] = new ChoiceControl(NULL, $series);
	$input->setDisabled();

	Assert::true( $form->isValid() );
	Assert::null( $input->getValue() );
	Assert::false( $input->isFilled() );
});


test(function() use ($series) { // malformed data
	$_POST = array('malformed' => array(NULL));

	$form = new Form;
	$input = $form['malformed'] = new ChoiceControl(NULL, $series);

	Assert::true( $form->isValid() );
	Assert::null( $input->getValue() );
	Assert::null( $input->getSelectedItem() );
	Assert::false( $input->isFilled() );
});


test(function() use ($series) { // setItems without keys
	$_POST = array('select' => 'red-dwarf');

	$form = new Form;
	$input = $form['select'] = new ChoiceControl;
	$input->setItems(array_keys($series), FALSE);

	Assert::true( $form->isValid() );
	Assert::same( 'red-dwarf', $input->getValue() );
	Assert::same( 'red-dwarf', $input->getSelectedItem() );
	Assert::true( $input->isFilled() );
});


test(function() use ($series) { // setValue() and invalid argument
	$form = new Form;
	$input = $form['select'] = new ChoiceControl(NULL, $series);
	$input->setValue(NULL);

	Assert::exception(function() use ($input) {
		$input->setValue('unknown');
	}, 'Nette\InvalidArgumentException', "Value 'unknown' is out of allowed range in field 'select'.");
});


test(function() { // object as value
	$form = new Form;
	$input = $form['select'] = new ChoiceControl(NULL, array('2013-07-05 00:00:00' => 1));
	$input->setValue(new DateTime('2013-07-05'));

	Assert::same( '2013-07-05 00:00:00', $input->getValue() );
});


test(function() { // object as item
	$form = new Form;
	$input = $form['select'] = new ChoiceControl;
	$input->setItems(array(new DateTime('2013-07-05')), FALSE)
		->setValue(new DateTime('2013-07-05'));

	Assert::same( '2013-07-05 00:00:00', $input->getValue() );
});


test(function() use ($series) { // disabled one
	$_POST = array('select' => 'red-dwarf');

	$form = new Form;
	$input = $form['select'] = new ChoiceControl(NULL, $series);
	$input->setDisabled(array('red-dwarf'));

	Assert::null( $input->getValue() );

	unset($form['select']);
	$input = new ChoiceControl(NULL, $series);
	$input->setDisabled(array('red-dwarf'));
	$form['select'] = $input;

	Assert::null( $input->getValue() );
});
