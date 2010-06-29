<?php

/**
 * Test: TestHelpers::getSection()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Test
 * @subpackage UnitTests
 */

require __DIR__ . '/initialize.php';



TestHelpers::dump( TestHelpers::getSection(__FILE__, 'MySection') );

TestHelpers::dump( TestHelpers::getSection(__FILE__, 'mysection') );

TestHelpers::dump( TestHelpers::getSection(__FILE__, 'POST') );

TestHelpers::dump( TestHelpers::getSection(__FILE__, 'options') );



__halt_compiler() ?>

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
	"name" => string(25) "TestHelpers::getSection()"
}
