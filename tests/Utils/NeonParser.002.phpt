<?php

/**
 * Test: Nette\NeonParser inline hash and array.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\NeonParser;



require __DIR__ . '/../initialize.php';



$parser = new NeonParser;

T::dump( $parser->parse('[true, tRuE, TRUE, false, FALSE, yes, YES, no, NO, null, NULL,]') );

T::dump( $parser->parse('{true: 1, false: 1, null: 1, -5: 1, 5.3: 1}') );

T::dump( $parser->parse('{a, b, {c: d}, e: f,}') );



__halt_compiler() ?>

------EXPECT------
array(
	TRUE
	"tRuE"
	TRUE
	FALSE
	FALSE
	TRUE
	TRUE
	FALSE
	FALSE
	NULL
	NULL
)

array(
	1 => 1
	"" => 1
	-5 => 1
	"5.3" => 1
)

array(
	0 => "a"
	1 => "b"
	2 => array(
		"c" => "d"
	)
	"e" => "f"
)
