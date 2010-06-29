<?php

/**
 * Test: Nette\Templates\LatteFilter::formatModifiers()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\LatteFilter;



require __DIR__ . '/../initialize.php';



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
