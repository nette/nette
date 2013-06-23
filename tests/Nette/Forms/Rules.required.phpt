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
});
