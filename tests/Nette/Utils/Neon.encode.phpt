<?php

/**
 * Test: Nette\Utils\Neon::encode.
 *
 * @author     David Grudl
 * @package    Nette\Utils
 */

use Nette\Utils\Neon;


require __DIR__ . '/../bootstrap.php';


Assert::same(
	'[true, "TRUE", "tRuE", "true", false, "FALSE", "fAlSe", "false", null, "NULL", "nUlL", "null", "yes", "no", "on", "off"]',
	Neon::encode(array(
		TRUE, 'TRUE', 'tRuE', 'true',
		FALSE, 'FALSE', 'fAlSe', 'false',
		NULL, 'NULL', 'nUlL', 'null',
		'yes', 'no', 'on', 'off',
)) );

Assert::same(
	'[1, 1.0, 0, 0.0, -1, -1.2, "1", "1.0", "-1"]',
	Neon::encode(array(1, 1.0, 0, 0.0, -1, -1.2, '1', '1.0', '-1'))
);

Assert::same(
	'["[", "]", "{", "}", ":", ": ", "=", "#"]',
	Neon::encode(array('[', ']', '{', '}', ':', ': ', '=', '#'))
);

Assert::same(
	'[1, 2, 3]',
	Neon::encode(array(1, 2, 3))
);

Assert::same(
	'{1: 1, 2: 2, 3: 3}',
	Neon::encode(array(1 => 1, 2, 3))
);

Assert::same(
	'{foo: 1, bar: [2, 3]}',
	Neon::encode(array('foo' => 1, 'bar' => array(2, 3)))
);

Assert::same(
	'item(a, b)',
	Neon::encode(Neon::decode('item(a, b)'))
);

Assert::same(
	'item<item>(a, b)',
	Neon::encode(Neon::decode('item<item>(a, b)'))
);

Assert::same(
	'item(foo: a, bar: b)',
	Neon::encode(Neon::decode('item(foo: a, bar: b)'))
);

Assert::same(
	'[]()',
	Neon::encode(Neon::decode('[]()'))
);
