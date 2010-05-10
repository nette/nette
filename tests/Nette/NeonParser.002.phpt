<?php

/**
 * Test: Nette\NeonParser inline hash and array.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

/*use Nette\NeonParser;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$parser = new NeonParser;

dump( $parser->parse('[true, tRuE, TRUE, false, FALSE, yes, YES, no, NO, null, NULL,]') );

dump( $parser->parse('{true: 1, false: 1, null: 1, -5: 1, 5.3: 1}') );

dump( $parser->parse('{a, b, {c: d}, e: f,}') );



__halt_compiler();

------EXPECT------
array(11) {
	0 => bool(TRUE)
	1 => string(4) "tRuE"
	2 => bool(TRUE)
	3 => bool(FALSE)
	4 => bool(FALSE)
	5 => bool(TRUE)
	6 => bool(TRUE)
	7 => bool(FALSE)
	8 => bool(FALSE)
	9 => NULL
	10 => NULL
}

array(4) {
	1 => int(1)
	"" => int(1)
	-5 => int(1)
	"5.3" => int(1)
}

array(4) {
	0 => string(1) "a"
	1 => string(1) "b"
	2 => array(1) {
		"c" => string(1) "d"
	}
	"e" => string(1) "f"
}
