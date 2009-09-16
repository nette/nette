<?php

/**
 * Test: Nette\Templates\LatteFilter::formatModifiers()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

/*use Nette\Templates\LatteFilter;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



output('special');

dump( LatteFilter::formatModifiers('@', '') ); // '@'
dump( LatteFilter::formatModifiers('@', ':') ); // '@'
dump( LatteFilter::formatModifiers('@', '|') ); // '@'
dump( LatteFilter::formatModifiers('@', 'mod::||:|') ); // '$template->mod(@)'

output('common');

dump( LatteFilter::formatModifiers('@', 'mod') ); // '$template->mod(@)'
dump( LatteFilter::formatModifiers('@', 'mod1|mod2|mod3') ); // '$template->mod3($template->mod2($template->mod1(@)))'

output('arguments');

dump( LatteFilter::formatModifiers('@', 'mod:arg1:2:$var["pocet"]') ); // '$template->mod(@, "arg1", 2, $var["pocet"])'
dump( LatteFilter::formatModifiers('@', 'mod,arg1,2,$var["pocet"]') ); // '$template->mod(@, "arg1", 2, $var["pocet"])'
dump( LatteFilter::formatModifiers('@', 'mod:" :a:b:c":"":3:""') ); // '$template->mod(@, " :a:b:c", "", 3, "")'
dump( LatteFilter::formatModifiers('@', 'mod:"\\":a:b:c"') ); // '$template->mod(@, "\":a:b:c")'
dump( LatteFilter::formatModifiers('@', "mod:'\\':a:b:c'") ); // "\$template->mod(@, '\':a:b:c')"
dump( LatteFilter::formatModifiers('@', "mod:'\\\\':a:b:c':arg2") ); // '$template->mod(@, \'\\\\\', "a", "b", "c", "arg2")'



__halt_compiler();

------EXPECT------
special

string(1) "@"

string(1) "@"

string(1) "@"

string(17) "$template->mod(@)"

common

string(17) "$template->mod(@)"

string(52) "$template->mod3($template->mod2($template->mod1(@)))"

arguments

string(43) "$template->mod(@, "arg1", 2, $var["pocet"])"

string(43) "$template->mod(@, "arg1", 2, $var["pocet"])"

string(39) "$template->mod(@, " :a:b:c", "", 3, "")"

string(29) "$template->mod(@, "\":a:b:c")"

string(29) "$template->mod(@, '\':a:b:c')"

string(46) "$template->mod(@, '\\', "a", "b", "c", "arg2")"
