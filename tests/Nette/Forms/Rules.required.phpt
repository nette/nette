<?php

/**
 * Test: Nette\Forms\Rules.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Form,
	Nette\Forms\Rule;


require __DIR__ . '/../bootstrap.php';


test(function() { // BaseControl
	$form = new Form;
	$input = $form->addText('text');

	Assert::false( $input->isRequired() );
	Assert::same( $input, $input->setRequired() );
	Assert::true( $input->isRequired() );
});


test(function() { // Rules
	$form = new Form;
	$input = $form->addText('text');
	$rules = $input->getRules();

	Assert::false( $rules->isRequired() );
	Assert::same( $rules, $rules->setRequired() );
	Assert::true( $rules->isRequired() );

	$items = iterator_to_array($rules);
	Assert::same( 1, count($items) );
	Assert::same( Form::REQUIRED, $items[0]->operation );
	Assert::same( Rule::VALIDATOR, $items[0]->type );
	Assert::false( $items[0]->isNegative );

	Assert::same( array('Please complete mandatory field.'), $rules->validate() );
});


test(function() { // 'required' is always the first rule
	$form = new Form;
	$rules = $form->addText('text')->getRules();

	$rules->addRule($form::EMAIL);
	$rules->addRule($form::REQUIRED);

	$items = iterator_to_array($rules);
	Assert::same( 2, count($items) );
	Assert::same( Form::REQUIRED, $items[0]->operation );
	Assert::same( Form::EMAIL, $items[1]->operation );

	$rules->addRule(~$form::REQUIRED);
	$items = iterator_to_array($rules);
	Assert::same( 2, count($items) );
	Assert::same( Form::REQUIRED, $items[0]->operation );
	Assert::true( $items[0]->isNegative );
	Assert::same( Form::EMAIL, $items[1]->operation );

	Assert::same( array('Please enter a valid email address.'), $rules->validate() );
});


test(function() { // setRequired(FALSE)
	$form = new Form;
	$rules = $form->addText('text')->getRules();

	$rules->addRule($form::EMAIL);
	$rules->addRule($form::REQUIRED);
	$rules->setRequired(FALSE);

	$items = iterator_to_array($rules);
	Assert::same( 1, count($items) );
	Assert::same( Form::EMAIL, $items[0]->operation );

	Assert::same( array('Please enter a valid email address.'), $rules->validate() );
});
