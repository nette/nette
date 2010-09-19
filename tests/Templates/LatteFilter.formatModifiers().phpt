<?php

/**
 * Test: Nette\Templates\LatteFilter::formatModifiers()
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\LatteFilter;



require __DIR__ . '/../bootstrap.php';



// special

Assert::same( '@',  LatteFilter::formatModifiers('@', '') );
Assert::same( '@',  LatteFilter::formatModifiers('@', ':') );
Assert::same( '@',  LatteFilter::formatModifiers('@', '|') );
Assert::same( '$template->mod(@)',  LatteFilter::formatModifiers('@', 'mod::||:|') );

// common

Assert::same( '$template->mod(@)',  LatteFilter::formatModifiers('@', 'mod') );
Assert::same( '$template->mod3($template->mod2($template->mod1(@)))',  LatteFilter::formatModifiers('@', 'mod1|mod2|mod3') );

// arguments

Assert::same( '$template->mod(@, "arg1", 2, $var["pocet"])',  LatteFilter::formatModifiers('@', 'mod:arg1:2:$var["pocet"]') );
Assert::same( '$template->mod(@, "arg1", 2, $var["pocet"])',  LatteFilter::formatModifiers('@', 'mod,arg1,2,$var["pocet"]') );
Assert::same( '$template->mod(@, " :a:b:c", "", 3, "")',  LatteFilter::formatModifiers('@', 'mod:" :a:b:c":"":3:""') );
Assert::same( '$template->mod(@, "\":a:b:c")',  LatteFilter::formatModifiers('@', 'mod:"\\":a:b:c"') );
Assert::same( "\$template->mod(@, '\':a:b:c')",  LatteFilter::formatModifiers('@', "mod:'\\':a:b:c'") );
Assert::same( '$template->mod(@, \'\\\\\', "a", "b", "c", "arg2")',  LatteFilter::formatModifiers('@', "mod:'\\\\':a:b:c':arg2") );
Assert::same( '$template->mod(@, "param", "param")',  LatteFilter::formatModifiers('@', 'mod : param : param') );
Assert::same( '$template->mod(@, $var, 0, -0.0, "str", \'str\')',  LatteFilter::formatModifiers('@', 'mod, $var, 0, -0.0, "str", \'str\'') );
Assert::same( '$template->mod(@, true, false, null)',  LatteFilter::formatModifiers('@', 'mod: true, false, null') );
Assert::same( '$template->mod(@, TRUE, FALSE, NULL)',  LatteFilter::formatModifiers('@', 'mod: TRUE, FALSE, NULL') );
Assert::same( '$template->mod(@, True, False, Null)',  LatteFilter::formatModifiers('@', 'mod: True, False, Null') );

Assert::same( '$template->mod(@, "array(1)")',  LatteFilter::formatModifiers('@', 'mod , array(1)') );
