<?php

/**
 * Test: Nette\Forms\Controls\TextBase validators.
 */

use Nette\Forms\Controls\TextInput,
	Nette\Forms\Controls\TextBase,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test(function() {
	$control = new TextInput();
	$control->value = '';
	Assert::true( TextBase::validateMinLength($control, 0) );
	Assert::false( TextBase::validateMinLength($control, 1) );
});


test(function() {
	$control = new TextInput();
	$control->value = '';
	Assert::true( TextBase::validateMaxLength($control, 0) );

	$control->value = 'aaa';
	Assert::false( TextBase::validateMaxLength($control, 2) );
	Assert::true( TextBase::validateMaxLength($control, 3) );
});


test(function() {
	$control = new TextInput();
	$control->value = '';
	Assert::true( TextBase::validateLength($control, 0) );
	Assert::true( TextBase::validateLength($control, array(0, 0)) );

	$control->value = 'aaa';
	Assert::true( TextBase::validateLength($control, 3) );
	Assert::false( TextBase::validateLength($control, 4) );
	Assert::true( TextBase::validateLength($control, array(3, )) );
	Assert::false( TextBase::validateLength($control, array(5, 6)) );
});


test(function() {
	$control = new TextInput();
	$control->value = '';
	Assert::false( TextBase::validateEmail($control) );

	$control->value = '@.';
	Assert::false( TextBase::validateEmail($control) );

	$control->value = 'name@a-b-c.cz';
	Assert::true( TextBase::validateEmail($control) );

	$control->value = "name@\xc5\xbelu\xc5\xa5ou\xc4\x8dk\xc3\xbd.cz"; // name@žluťoučký.cz
	Assert::true( TextBase::validateEmail($control) );

	$control->value = "\xc5\xbename@\xc5\xbelu\xc5\xa5ou\xc4\x8dk\xc3\xbd.cz"; // žname@žluťoučký.cz
	Assert::false( TextBase::validateEmail($control) );
});


test(function() {
	$control = new TextInput();
	$control->value = '';
	Assert::false( TextBase::validateUrl($control) );
	Assert::same( '', $control->value );

	$control->value = 'localhost';
	Assert::true( TextBase::validateUrl($control) );
	Assert::same( 'http://localhost', $control->value );

	$control->value = 'http://nette.org';
	Assert::true( TextBase::validateUrl($control) );
	Assert::same( 'http://nette.org', $control->value );

	$control->value = '/nette.org';
	Assert::false( TextBase::validateUrl($control) );
});


test(function() {
	$control = new TextInput();
	$control->value = '123x';
	//Assert::true( TextBase::validateRegExp($control, '/[0-9]/') );
	//Assert::false( TextBase::validateRegExp($control, '/a/') );
	Assert::false( TextBase::validatePattern($control, '[0-9]') );
	Assert::true( TextBase::validatePattern($control, '[0-9]+x') );
	Assert::false( TextBase::validatePattern($control, '[0-9]+X') );
});


test(function() {
	$control = new TextInput();
	$control->value = '';
	Assert::false( TextBase::validateInteger($control) );
	Assert::same( '', $control->value );

	$control->value = '-123';
	Assert::true( TextBase::validateInteger($control) );
	Assert::same( -123, $control->value );

	$control->value = '123,5';
	Assert::false( TextBase::validateInteger($control) );
	Assert::same( '123,5', $control->value );

	$control->value = '123.5';
	Assert::false( TextBase::validateInteger($control) );
	Assert::same( '123.5', $control->value );

	$control->value = PHP_INT_MAX . PHP_INT_MAX;
	Assert::true( TextBase::validateInteger($control) );
	Assert::same( PHP_INT_MAX . PHP_INT_MAX, $control->value );
});


test(function() {
	$control = new TextInput();
	$control->value = '';
	Assert::false( TextBase::validateFloat($control) );
	Assert::same( '', $control->value );

	$control->value = '-123';
	Assert::true( TextBase::validateFloat($control) );
	Assert::same( -123.0, $control->value );

	$control->value = '123,5';
	Assert::true( TextBase::validateFloat($control) );
	Assert::same( 123.5, $control->value );

	$control->value = '123.5';
	Assert::true( TextBase::validateFloat($control) );
	Assert::same( 123.5, $control->value );

	$control->value = PHP_INT_MAX . PHP_INT_MAX;
	Assert::true( TextBase::validateFloat($control) );
	Assert::same( (float) (PHP_INT_MAX . PHP_INT_MAX), $control->value );
});
