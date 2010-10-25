<?php

/**
 * Test: Nette\Templates\LatteMacros::formatModifiers()
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\LatteMacros;



require __DIR__ . '/../bootstrap.php';



$latte = new LatteMacros;

// special

Assert::same( '@',  $latte->formatModifiers('@', '') );
Assert::same( '@',  $latte->formatModifiers('@', '|') );
try {
	$latte->formatModifiers('@', ':');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidStateException', 'Modifier name must be alphanumeric string%a%', $e );
}
try {
	$latte->formatModifiers('@', 'mod::||:|');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidStateException', 'Modifier name must be alphanumeric string%a%', $e );
}
try {
	Assert::same( '$template->mod(@, \'\\\\\', "a", "b", "c", "arg2")',  $latte->formatModifiers('@', "mod:'\\\\':a:b:c':arg2") );
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\TokenizerException', 'Unexpected %a% on line 1, column 15.', $e );
}

// common

Assert::same( '$template->mod(@)',  $latte->formatModifiers('@', 'mod') );
Assert::same( '$template->mod3($template->mod2($template->mod1(@)))',  $latte->formatModifiers('@', 'mod1|mod2|mod3') );

// arguments

Assert::same( '$template->mod(@, \'arg1\', 2, $var["pocet"])',  $latte->formatModifiers('@', 'mod:arg1:2:$var["pocet"]') );
Assert::same( '$template->mod(@, \'arg1\', 2, $var["pocet"])',  $latte->formatModifiers('@', 'mod,arg1,2,$var["pocet"]') );
Assert::same( '$template->mod(@, " :a:b:c", "", 3, "")',  $latte->formatModifiers('@', 'mod:" :a:b:c":"":3:""') );
Assert::same( '$template->mod(@, "\":a:b:c")',  $latte->formatModifiers('@', 'mod:"\\":a:b:c"') );
Assert::same( "\$template->mod(@, '\':a:b:c')",  $latte->formatModifiers('@', "mod:'\\':a:b:c'") );
Assert::same( '$template->mod(@ , \'param\' , \'param\')',  $latte->formatModifiers('@', 'mod : param : param') );
Assert::same( '$template->mod(@, $var, 0, -0.0, "str", \'str\')',  $latte->formatModifiers('@', 'mod, $var, 0, -0.0, "str", \'str\'') );
Assert::same( '$template->mod(@, true, false, null)',  $latte->formatModifiers('@', 'mod: true, false, null') );
Assert::same( '$template->mod(@, TRUE, FALSE, NULL)',  $latte->formatModifiers('@', 'mod: TRUE, FALSE, NULL') );
Assert::same( '$template->mod(@, True, False, Null)',  $latte->formatModifiers('@', 'mod: True, False, Null') );
Assert::same( '$template->mod(@, array(1))',  $latte->formatModifiers('@', 'mod: array(1)') );
