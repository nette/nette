<?php

/**
 * Test: Nette\Forms\TextBase validators.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Forms
 * @subpackage UnitTests
 */

/*use Nette\Forms\TextBase;*/
/*use Nette\Forms\TextInput;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$control = new TextInput();
$control->value = '';
dump( TextBase::validateEmail($control) );

$control->value = '@.';
dump( TextBase::validateEmail($control) );

$control->value = 'name@a-b-c.cz';
dump( TextBase::validateEmail($control) );

$control->value = "name@\xc5\xbelu\xc5\xa5ou\xc4\x8dk\xc3\xbd.cz"; // name@ûluùouËk˝.cz
dump( TextBase::validateEmail($control) );

$control->value = "\xc5\xbename@\xc5\xbelu\xc5\xa5ou\xc4\x8dk\xc3\xbd.cz"; // ûname@ûluùouËk˝.cz
dump( TextBase::validateEmail($control) );



__halt_compiler();

------EXPECT------
bool(FALSE)

bool(FALSE)

bool(TRUE)

bool(TRUE)

bool(FALSE)
