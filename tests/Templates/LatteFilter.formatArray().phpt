<?php

/**
 * Test: Nette\Templates\LatteFilter::formatArray()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

/*use Nette\Templates\LatteFilter;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



output('symbols');

dump( LatteFilter::formatArray('') ); // ''
dump( LatteFilter::formatArray('', '&') ); // ''
dump( LatteFilter::formatArray('1') ); // 'array(1)'
dump( LatteFilter::formatArray('1', '&') ); // '&array(1)'
dump( LatteFilter::formatArray('symbol') ); // "array('symbol')"
dump( LatteFilter::formatArray('1, 2, symbol1, symbol2') ); // "array(1, 2,'symbol1','symbol2')"

output('strings');

dump( LatteFilter::formatArray('"\"1, 2, symbol1, symbol2"') ); // 'array("\"1, 2, symbol1, symbol2")' // unable to parse "${'"'}" yet
dump( LatteFilter::formatArray("'\\'1, 2, symbol1, symbol2'") ); // "array('\\'1, 2, symbol1, symbol2')"
dump( LatteFilter::formatArray("'\\\\'1, 2, symbol1, symbol2'") ); // "array('\\\\'1, 2,'symbol1', symbol2')"

output('key words');

dump( LatteFilter::formatArray('TRUE, false, null, 1 or 1 and 2 xor 3, clone $obj, new Class') ); // 'array(TRUE, false, null, 1 or 1 and 2 xor 3, clone $obj, new Class)'
dump( LatteFilter::formatArray('func (10)') ); // 'array(func (10))'

output('associative arrays');

dump( LatteFilter::formatArray('symbol1 => value,symbol2=>value') ); // "array('symbol1' =>'value','symbol2'=>'value')"
dump( LatteFilter::formatArray('symbol1 => array (symbol2 => value)') ); // "array('symbol1' => array ('symbol2' =>'value'))"



__halt_compiler();

------EXPECT------
symbols

string(0) ""

string(0) ""

string(8) "array(1)"

string(9) "&array(1)"

string(15) "array('symbol')"

string(31) "array(1, 2,'symbol1','symbol2')"

strings

string(33) "array("\"1, 2, symbol1, symbol2")"

string(33) "array('\'1, 2, symbol1, symbol2')"

string(35) "array('\\'1, 2,'symbol1', symbol2')"

key words

string(67) "array(TRUE, false, null, 1 or 1 and 2 xor 3, clone $obj, new Class)"

string(16) "array(func (10))"

associative arrays

string(45) "array('symbol1' =>'value','symbol2'=>'value')"

string(47) "array('symbol1' => array ('symbol2' =>'value'))"
