<?php

/**
 * Test: Nette\Forms\Controls\CsrfProtection.
 */

use Nette\Forms\Controls\CsrfProtection;
use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$_SERVER['REQUEST_METHOD'] = 'POST';


$form = new Form;

$input = $form->addProtection('Security token did not match. Possible CSRF attack.');

$form->fireEvents();

Assert::same(array('Security token did not match. Possible CSRF attack.'), $form->getErrors());
Assert::match('<input type="hidden" name="_token_" id="frm-_token_" value="%S%">', (string) $input->getControl());

$input->setValue(NULL);
Assert::false(CsrfProtection::validateCsrf($input));

call_user_func(array($input, 'Nette\Forms\Controls\BaseControl::setValue'), '12345678901234567890123456789012345678');
Assert::false(CsrfProtection::validateCsrf($input));

$value = $input->getControl()->value;
call_user_func(array($input, 'Nette\Forms\Controls\BaseControl::setValue'), $value);
Assert::true(CsrfProtection::validateCsrf($input));

session_regenerate_id();
call_user_func(array($input, 'Nette\Forms\Controls\BaseControl::setValue'), $value);
Assert::false(CsrfProtection::validateCsrf($input));
