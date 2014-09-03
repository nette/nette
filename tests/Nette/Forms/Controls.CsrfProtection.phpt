<?php

/**
 * Test: Nette\Forms\Controls\CsrfProtection.
 */

use Nette\Forms\Controls\CsrfProtection,
	Nette\Forms\Form,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$_SERVER['REQUEST_METHOD'] = 'POST';


$form = new Form;

$form->addProtection('Security token did not match. Possible CSRF attack.');
$input = $form[Form::PROTECTOR_ID];

$form->fireEvents();

Assert::same( array('Security token did not match. Possible CSRF attack.'), $form->getErrors() );
Assert::match('<input type="hidden" name="_token_" id="frm-_token_" value="%S%" />', (string) $input->getControl());

$input->setValue(NULL);
$form->validate();
Assert::false( $form->isValid() );

$input->setValue('12345678901234567890123456789012345678');
$form->validate();
Assert::false( $form->isValid() );

$value = $input->getControl()->value;
$input->setValue($value);
$form->validate();
Assert::true( $form->isValid() );

session_regenerate_id();
$form = new Form;
$form->addProtection('Security token did not match. Possible CSRF attack.');
$form[Form::PROTECTOR_ID]->setValue($value);
Assert::false( $form->isValid() );
