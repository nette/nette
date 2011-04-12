<?php

/**
 * Test: Nette\NeonParser inline hash and array.
 *
 * @author     David Grudl
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Neon;



require __DIR__ . '/../bootstrap.php';



Assert::same( array(
	TRUE,
	'tRuE',
	TRUE,
	FALSE,
	FALSE,
	TRUE,
	TRUE,
	FALSE,
	FALSE,
	NULL,
	NULL,
), Neon::decode('[true, tRuE, TRUE, false, FALSE, yes, YES, no, NO, null, NULL,]') );


Assert::same( array(
	1 => 1,
	'' => 1,
	-5 => 1,
	'5.3' => 1,
), Neon::decode('{true: 1, false: 1, null: 1, -5: 1, 5.3: 1}') );


Assert::same( array(
	0 => 'a',
	1 => 'b',
	2 => array(
		'c' => 'd',
	),
	'e' => 'f',
), Neon::decode('{a, b, {c: d}, e: f,}') );
