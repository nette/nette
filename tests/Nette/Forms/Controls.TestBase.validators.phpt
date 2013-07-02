<?php

/**
 * Test: Nette\Forms\Controls\TextBase validators.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Controls\TextInput,
	Nette\Forms\Validator;


require __DIR__ . '/../bootstrap.php';


test(function() {
	$control = new TextInput();
	$control->value = '';
	Assert::true( Validator::validateMinLength($control, 0) );
	Assert::false( Validator::validateMinLength($control, 1) );
});


test(function() {
	$control = new TextInput();
	$control->value = '';
	Assert::true( Validator::validateMaxLength($control, 0) );

	$control->value = 'aaa';
	Assert::false( Validator::validateMaxLength($control, 2) );
	Assert::true( Validator::validateMaxLength($control, 3) );
});


test(function() {
	$control = new TextInput();
	$control->value = '';
	Assert::true( Validator::validateLength($control, 0) );
	Assert::true( Validator::validateLength($control, array(0, 0)) );

	$control->value = 'aaa';
	Assert::true( Validator::validateLength($control, 3) );
	Assert::false( Validator::validateLength($control, 4) );
	Assert::true( Validator::validateLength($control, array(3, )) );
	Assert::false( Validator::validateLength($control, array(5, 6)) );
});


test(function() {
	$control = new TextInput();
	$control->value = '';
	Assert::false( Validator::validateEmail($control) );

	$control->value = '@.';
	Assert::false( Validator::validateEmail($control) );

	$control->value = 'name@a-b-c.cz';
	Assert::true( Validator::validateEmail($control) );

	$control->value = "name@\xc5\xbelu\xc5\xa5ou\xc4\x8dk\xc3\xbd.cz"; // name@žluťoučký.cz
	Assert::true( Validator::validateEmail($control) );

	$control->value = "\xc5\xbename@\xc5\xbelu\xc5\xa5ou\xc4\x8dk\xc3\xbd.cz"; // žname@žluťoučký.cz
	Assert::false( Validator::validateEmail($control) );
});


test(function() {
	$control = new TextInput();
	$control->value = '';
	Assert::false( Validator::validateUrl($control) );

	$control->value = 'localhost';
	Assert::true( Validator::validateUrl($control) );

	$control->value = 'http://nette.org';
	Assert::true( Validator::validateUrl($control) );

	$control->value = '/nette.org';
	Assert::false( Validator::validateUrl($control) );
});


test(function() {
	$control = new TextInput();
	$control->value = '123x';
	//Assert::true( Validator::validateRegExp($control, '/[0-9]/') );
	//Assert::false( Validator::validateRegExp($control, '/a/') );
	Assert::false( Validator::validatePattern($control, '[0-9]') );
	Assert::true( Validator::validatePattern($control, '[0-9]+x') );
	Assert::false( Validator::validatePattern($control, '[0-9]+X') );
});


test(function() {
	$control = new TextInput();
	$control->value = '';
	Assert::false( Validator::validateInteger($control) );
	Assert::false( Validator::validateFloat($control) );

	$control->value = '-123';
	Assert::true( Validator::validateInteger($control) );
	Assert::true( Validator::validateFloat($control) );

	$control->value = '123,5';
	Assert::false( Validator::validateInteger($control) );
	Assert::true( Validator::validateFloat($control) );

	$control->value = '123.5';
	Assert::false( Validator::validateInteger($control) );
	Assert::true( Validator::validateFloat($control) );
});
