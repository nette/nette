<?php

/**
 * Test: Nette\Forms\Controls\TextBase validators.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Controls\TextInput,
	Nette\Forms\Controls\TextBase;


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

	$control->value = 'localhost';
	Assert::true( TextBase::validateUrl($control) );

	$control->value = 'http://nette.org';
	Assert::true( TextBase::validateUrl($control) );

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
	Assert::false( TextBase::validateFloat($control) );

	$control->value = '-123';
	Assert::true( TextBase::validateInteger($control) );
	Assert::true( TextBase::validateFloat($control) );

	$control->value = '123,5';
	Assert::false( TextBase::validateInteger($control) );
	Assert::true( TextBase::validateFloat($control) );

	$control->value = '123.5';
	Assert::false( TextBase::validateInteger($control) );
	Assert::true( TextBase::validateFloat($control) );
});
