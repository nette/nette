<?php

/**
 * Test: Nette\Database\Row.
 *
 * @author     David Grudl
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

use Tester\Assert;

require __DIR__ . '/connect.inc.php'; // create $connection


test(function() use ($context) {
	// numeric field
	$row = $context->fetch("SELECT 123 AS {$context->getConnection()->getSupplementalDriver()->delimite('123')}, NULL as nullcol");
	Assert::same(123, $row->{123});
	Assert::same(123, $row->{'123'});
	Assert::true(isset($row->{123}));
	Assert::false(isset($row->{1}));

	Assert::same(123, $row[0]);
	Assert::true(isset($row[0]));
	Assert::false(isset($row[123]));
	//Assert::false(isset($row['0'])); // this is buggy since PHP 5.4 (bug #63217)
	Assert::false(isset($row[1])); // NULL value
	Assert::false(isset($row[2])); // is not set


	Assert::error(function () use ($row) {
		$row->{2};
	}, 'Nette\MemberAccessException', 'Cannot read an undeclared column "2".');

	Assert::error(function () use ($row) {
		$row[2];
	}, 'Nette\MemberAccessException', 'Cannot read an undeclared column "2".');
});
