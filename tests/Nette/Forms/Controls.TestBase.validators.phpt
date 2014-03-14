<?php

/**
 * Test: Nette\Forms\Controls\TextBase validators.
 *
 * @author     David Grudl
 */

use Nette\Forms\Controls\TextInput,
	Nette\Forms\Validator,
	Tester\Assert;


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
	Assert::same( '', $control->value );

	$control->value = 'localhost';
	Assert::true( Validator::validateUrl($control) );
	Assert::same( 'http://localhost', $control->value );

	$control->value = 'http://nette.org';
	Assert::true( Validator::validateUrl($control) );
	Assert::same( 'http://nette.org', $control->value );

	$control->value = '/nette.org';
	Assert::false( Validator::validateUrl($control) );
});


test(function() {
	$control = new TextInput();
	$control->value = '123x';
	Assert::false( Validator::validatePattern($control, '[0-9]') );
	Assert::true( Validator::validatePattern($control, '[0-9]+x') );
	Assert::false( Validator::validatePattern($control, '[0-9]+X') );
});


test(function() {
	$control = new TextInput();
	$control->value = '';
	Assert::false( Validator::validateInteger($control) );
	Assert::same( '', $control->value );

	$control->value = '-123';
	Assert::true( Validator::validateInteger($control) );
	Assert::same( -123, $control->value );

	$control->value = '123,5';
	Assert::false( Validator::validateInteger($control) );
	Assert::same( '123,5', $control->value );

	$control->value = '123.5';
	Assert::false( Validator::validateInteger($control) );
	Assert::same( '123.5', $control->value );

	$control->value = PHP_INT_MAX . PHP_INT_MAX;
	Assert::true( Validator::validateInteger($control) );
	Assert::same( PHP_INT_MAX . PHP_INT_MAX, $control->value );
});


test(function() {
	$control = new TextInput();
	$control->value = '';
	Assert::false( Validator::validateFloat($control) );
	Assert::same( '', $control->value );

	$control->value = '-123';
	Assert::true( Validator::validateFloat($control) );
	Assert::same( -123.0, $control->value );

	$control->value = '123,5';
	Assert::true( Validator::validateFloat($control) );
	Assert::same( 123.5, $control->value );

	$control->value = '123.5';
	Assert::true( Validator::validateFloat($control) );
	Assert::same( 123.5, $control->value );

	$control->value = PHP_INT_MAX . PHP_INT_MAX;
	Assert::true( Validator::validateFloat($control) );
	Assert::same( (float) (PHP_INT_MAX . PHP_INT_MAX), $control->value );
});
