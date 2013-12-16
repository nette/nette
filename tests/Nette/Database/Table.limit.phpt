<?php

/**
 * Test: Nette\Database\Table: limit.
 *
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

use Tester\Assert;

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");


Assert::same(
	$driverName === 'sqlsrv'
		? 'SELECT TOP 2 * FROM [author]'
		: reformat('SELECT * FROM [author] LIMIT 2'),
	$context->table('author')->limit(2)->getSql()
);

if ($driverName === 'sqlsrv') {
	Assert::exception(function() use ($context) {
		$context->table('author')->limit(2, 10)->getSql();
	}, 'Nette\NotSupportedException', 'Offset is not supported by this database.');
} else {
	Assert::same(
		reformat('SELECT * FROM [author] LIMIT 2 OFFSET 10'),
		$context->table('author')->limit(2, 10)->getSql()
	);
}

Assert::same(
	$driverName === 'sqlsrv'
		? 'SELECT TOP 2 * FROM [author] ORDER BY [name]'
		: reformat('SELECT * FROM [author] ORDER BY [name] LIMIT 2'),
	$context->table('author')->order('name')->limit(2)->getSql()
);

Assert::same(
	$driverName === 'sqlsrv'
		? 'SELECT TOP 10 * FROM [author]'
		: reformat('SELECT * FROM [author] LIMIT 10'),
	$context->table('author')->page(1, 10)->getSql()
);

Assert::same(
	$driverName === 'sqlsrv'
		? 'SELECT TOP 10 * FROM [author]'
		: reformat('SELECT * FROM [author] LIMIT 10'),
	$context->table('author')->page(0, 10)->getSql()
);

if ($driverName === 'sqlsrv') {
	Assert::exception(function() use ($context) {
		$context->table('author')->page(2, 10, $count)->getSql();
	}, 'Nette\NotSupportedException', 'Offset is not supported by this database.');
} else {
	Assert::same(
		reformat('SELECT * FROM [author] LIMIT 10 OFFSET 10'),
		$context->table('author')->page(2, 10, $count)->getSql()
	);
	Assert::same(1, $count);
}

if ($driverName === 'sqlsrv') {
	Assert::exception(function() use ($context) {
		$context->table('author')->page(2, 2, $count)->getSql();
	}, 'Nette\NotSupportedException', 'Offset is not supported by this database.');
} else {
	Assert::same(
		reformat('SELECT * FROM [author] LIMIT 2 OFFSET 2'),
		$context->table('author')->page(2, 2, $count)->getSql()
	);
	Assert::same(2, $count);
}
