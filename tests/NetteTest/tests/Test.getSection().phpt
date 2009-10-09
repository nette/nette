<?php

/**
 * Test: NetteTestHelpers::getSection()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Test
 * @subpackage UnitTests
 */

require dirname(__FILE__) . '/../initialize.php';



dump( NetteTestHelpers::getSection(__FILE__, 'MySection') );

dump( NetteTestHelpers::getSection(__FILE__, 'mysection') );

dump( NetteTestHelpers::getSection(__FILE__, 'POST') );

dump( NetteTestHelpers::getSection(__FILE__, 'options') );



__halt_compiler();

------MySection------
any
content

------POST------
key1 = value1
key2=value2

------EXPECT------
string(16) "any
content

"

string(16) "any
content

"

array(2) {
	"key1" => string(6) "value1"
	"key2" => string(6) "value2"
}

array(5) {
	"author" => string(11) "David Grudl"
	"category" => string(5) "Nette"
	"package" => string(10) "%ns%Test"
	"subpackage" => string(9) "UnitTests"
	"name" => string(30) "NetteTestHelpers::getSection()"
}
