<?php

/**
 * Test: Nette\Test::heading()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Test
 * @subpackage UnitTests
 */

require dirname(__FILE__) . '/../initialize.php';


dump( Test::getSection(__FILE__, 'MySection') );

dump( Test::getSection(__FILE__, 'mysection') );

dump( Test::getSection(__FILE__, 'POST') );

dump( Test::getSection(__FILE__, 'options') );


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
	"key2" => string(5) "alue2"
}

array(5) {
	"author" => string(11) "David Grudl"
	"category" => string(5) "Nette"
	"package" => string(10) "Nette\Test"
	"subpackage" => string(9) "UnitTests"
	"name" => string(21) "Nette\Test::heading()"
}

-------END-------
