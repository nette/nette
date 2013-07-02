<?php

/**
 * Test: Nette\Utils\Neon::decode inline hash and array.
 *
 * @author     David Grudl
 * @package    Nette\Utils
 */

use Nette\Utils\Neon;


require __DIR__ . '/../bootstrap.php';


Assert::same( array(
	'foo' => 'bar',
), Neon::decode('{"foo":"bar"}') );


Assert::same( array(
	TRUE, 'tRuE', TRUE, FALSE, FALSE, TRUE, TRUE, FALSE, FALSE, NULL, NULL,
), Neon::decode('[true, tRuE, TRUE, false, FALSE, yes, YES, no, NO, null, NULL,]') );


Assert::same( array(
	1 => 1,
	'' => 1,
	-5 => 1,
	'5.3' => 1,
), Neon::decode('{true: 1, false: 1, -5: 1, 5.3: 1}') );


Assert::same( array(
	0 => 'a',
	1 => 'b',
	2 => array(
		'c' => 'd',
	),
	'e' => 'f',
	'g' => NULL,
	'h' => NULL,
), Neon::decode('{a, b, {c: d}, e: f, g:,h:}') );


Assert::same( array(
	'a',
	'b',
	'c' => 1,
	'd' => 1,
	'e' => 1,
	'f' => NULL,
), Neon::decode("{a,\nb\nc: 1,\nd: 1,\n\ne: 1\nf:\n}") );


Assert::type( 'Nette\Utils\NeonEntity', Neon::decode('@item(a, b)') );


Assert::same( array(
	'value' => '@item',
	'attributes' => array('a', 'b'),
), (array) Neon::decode('@item(a, b)') );


Assert::same( array(
	'value' => '@item<item>',
	'attributes' => array('a', 'b'),
), (array) Neon::decode('@item<item>(a, b)') );


Assert::same( array(
	'value' => 'item',
	'attributes' => array('a', 'b'),
), (array) Neon::decode('item (a, b)') );


Assert::same( array(
	'value' => array(),
	'attributes' => array(),
), (array) Neon::decode('[]()') );
