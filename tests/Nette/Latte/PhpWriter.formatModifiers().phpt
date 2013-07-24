<?php

/**
 * Test: Nette\Latte\PhpWriter::formatModifiers()
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte\PhpWriter,
	Nette\Latte\MacroTokens;


require __DIR__ . '/../bootstrap.php';


function formatModifiers($arg, $modifiers) {
	$writer = new PhpWriter(new MacroTokens(''), $modifiers);
	return $writer->formatModifiers($arg);
}


test(function() { // special
	Assert::same( '@',  formatModifiers('@', '') );
	Assert::same( '@',  formatModifiers('@', '|') );
	Assert::exception(function() {
		formatModifiers('@', ':');
	}, 'Nette\InvalidStateException', 'Modifier name must be alphanumeric string%a%');
	Assert::exception(function() {
		Assert::same( '$template->mod(@, \'\\\\\', "a", "b", "c", "arg2")',  formatModifiers('@', "mod:'\\\\':a:b:c':arg2") );
	}, 'Nette\Utils\TokenizerException', 'Unexpected %a% on line 1, column 15.');
});


test(function() { // common
	Assert::same( '$template->mod(@)',  formatModifiers('@', 'mod') );
	Assert::same( '$template->mod3($template->mod2($template->mod1(@)))',  formatModifiers('@', 'mod1|mod2|mod3') );
});


test(function() { // arguments
	Assert::same( '$template->mod(@, \'arg1\', 2, $var["pocet"])',  formatModifiers('@', 'mod:arg1:2:$var["pocet"]') );
	Assert::same( '$template->mod(@, \'arg1\', 2, $var["pocet"])',  formatModifiers('@', 'mod,arg1,2,$var["pocet"]') );
	Assert::same( '$template->mod(@, " :a:b:c", "", 3, "")',  formatModifiers('@', 'mod:" :a:b:c":"":3:""') );
	Assert::same( '$template->mod(@, "\":a:b:c")',  formatModifiers('@', 'mod:"\\":a:b:c"') );
	Assert::same( "\$template->mod(@, '\':a:b:c')",  formatModifiers('@', "mod:'\\':a:b:c'") );
	Assert::same( '$template->mod(@ , \'param\' , \'param\')',  formatModifiers('@', 'mod : param : param') );
	Assert::same( '$template->mod(@, $var, 0, -0.0, "str", \'str\')',  formatModifiers('@', 'mod, $var, 0, -0.0, "str", \'str\'') );
	Assert::same( '$template->mod(@, true, false, null)',  formatModifiers('@', 'mod: true, false, null') );
	Assert::same( '$template->mod(@, TRUE, FALSE, NULL)',  formatModifiers('@', 'mod: TRUE, FALSE, NULL') );
	Assert::same( '$template->mod(@, \'True\', \'False\', \'Null\')',  formatModifiers('@', 'mod: True, False, Null') );
	Assert::same( '$template->mod(@, array(1))',  formatModifiers('@', 'mod: array(1)') );
});
