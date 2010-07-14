<?php

/**
 * Test: Nette\Forms\TextBase validators.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Forms
 * @subpackage UnitTests
 */

use Nette\Forms\TextBase,
	Nette\Forms\TextInput;



require __DIR__ . '/../initialize.php';



$control = new TextInput();
$control->value = '';
T::dump( TextBase::validateEmail($control) );

$control->value = '@.';
T::dump( TextBase::validateEmail($control) );

$control->value = 'name@a-b-c.cz';
T::dump( TextBase::validateEmail($control) );

$control->value = "name@\xc5\xbelu\xc5\xa5ou\xc4\x8dk\xc3\xbd.cz"; // name@žluouèký.cz
T::dump( TextBase::validateEmail($control) );

$control->value = "\xc5\xbename@\xc5\xbelu\xc5\xa5ou\xc4\x8dk\xc3\xbd.cz"; // žname@žluouèký.cz
T::dump( TextBase::validateEmail($control) );



__halt_compiler() ?>

------EXPECT------
FALSE

FALSE

TRUE

TRUE

FALSE
