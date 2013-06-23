<?php

/**
 * Test: Nette\Forms\Controls\BaseControl
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Form;



require __DIR__ . '/../bootstrap.php';


test(function() { // validation
	$form = new Form;
	$input = $form->addText('text')
		->addRule($form::EMAIL, 'error');

	Assert::same( array(), $input->getErrors() );
	Assert::null( $input->getError() );
	Assert::false( $input->hasErrors() );

	$input->validate();

	Assert::same( array('error'), $input->getErrors() );
	Assert::same( 'error', $input->getError() );
	Assert::true( $input->hasErrors() );

	$input->cleanErrors();
	Assert::false( $input->hasErrors() );
});
