<?php

/**
 * Test: Nette\Templates\LatteFilter::formatString()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

/*use Nette\Templates\LatteFilter;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



dump( LatteFilter::formatString('') ); // '""'
dump( LatteFilter::formatString(' ') ); // '" "'
dump( LatteFilter::formatString('0') ); // "0"
dump( LatteFilter::formatString('-0.0') ); // "-0.0"
dump( LatteFilter::formatString('symbol') ); // '"symbol"'
dump( LatteFilter::formatString('$var') ); // "\$var"
dump( LatteFilter::formatString('symbol$var') ); // '"symbol$var"'
dump( LatteFilter::formatString("'var'") ); // "'var'"
dump( LatteFilter::formatString('"var"') ); // '"var"'
dump( LatteFilter::formatString('"v\\"ar"') ); // '"v\\"ar"'
dump( LatteFilter::formatString("'var\"") ); // "'var\""



__halt_compiler();

------EXPECT------
string(2) """"

string(3) "" ""

string(1) "0"

string(4) "-0.0"

string(8) ""symbol""

string(4) "$var"

string(12) ""symbol$var""

string(5) "'var'"

string(5) ""var""

string(7) ""v\"ar""

string(5) "'var""
