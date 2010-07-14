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
"any
content

"

"any
content

"

array(
	"key1" => "value1"
	"key2" => "value2"
)

array(
	"author" => "David Grudl"
	"category" => "Nette"
	"package" => "%ns%Test"
	"subpackage" => "UnitTests"
	"name" => "TestHelpers::getSection()"
)
