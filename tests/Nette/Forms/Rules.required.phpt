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
	Assert::same( Form::REQUIRED, $items[0]->validator );
	Assert::null( $items[0]->branch );
	Assert::false( $items[0]->isNegative );

	Assert::false( $rules->validate() );
	Assert::same( array('This field is required.'), $input->getErrors() );
});


test(function() { // 'required' is always the first rule
	$form = new Form;
	$input = $form->addText('text');
	$rules = $input->getRules();

	$rules->addRule($form::EMAIL);
	$rules->addRule($form::REQUIRED);

	$items = iterator_to_array($rules);
	Assert::same( 2, count($items) );
	Assert::same( Form::REQUIRED, $items[0]->validator );
	Assert::same( Form::EMAIL, $items[1]->validator );

	$rules->addRule(~$form::REQUIRED);
	$items = iterator_to_array($rules);
	Assert::same( 2, count($items) );
	Assert::same( Form::REQUIRED, $items[0]->validator );
	Assert::true( $items[0]->isNegative );
	Assert::same( Form::EMAIL, $items[1]->validator );

	Assert::false( $rules->validate() );
	Assert::same( array('Please enter a valid email address.'), $input->getErrors() );
});


test(function() { // setRequired(FALSE)
	$form = new Form;
	$input = $form->addText('text');
	$rules = $input->getRules();

	$rules->addRule($form::EMAIL);
	$rules->addRule($form::REQUIRED);
	$rules->setRequired(FALSE);

	$items = iterator_to_array($rules);
	Assert::same( 1, count($items) );
	Assert::same( Form::EMAIL, $items[0]->validator );

	Assert::false( $rules->validate() );
	Assert::same( array('Please enter a valid email address.'), $input->getErrors() );
});
