<?php

/**
 * Test: Nette\Utils\Neon::decode block hash and array.
 *
 * @author     David Grudl
 * @package    Nette\Utils
 */

use Nette\Utils\Neon;


require __DIR__ . '/../bootstrap.php';


Assert::same( array(
	'a' => array(1, 2),
	'b' => 1,
), Neon::decode('
a: {1, 2, }
b: 1') );


Assert::same( array(
	'a' => 'x',
	'x',
), Neon::decode('
a: x
- x') );


Assert::same( array(
	'x',
	'a' => 'x',
), Neon::decode('
- x
a: x
') );


Assert::same( array(
	'x' => array(
		'x',
		'a' => 'x',
	),
), Neon::decode('
x:
	- x
	a: x
') );


Assert::same( array(
	'x' => array(
		'y' => array(
			NULL,
		),
		'a' => 'x',
	),
), Neon::decode('
x:
	y:
		-
	a: x
') );


Assert::same( array(
	'x' => array(
		'a' => 1,
		'b' => 2,
	),
), Neon::decode('
x: {
	a: 1
b: 2
}
') );


Assert::same( array(
	'one',
	'two',
), Neon::decode('
{
	one
two
}
') );
