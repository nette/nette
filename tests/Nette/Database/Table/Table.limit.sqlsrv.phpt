<?php

/**
 * Test: Nette\Database\Table: limit.
 * @dataProvider? ../databases.ini, sqlsrv
 */

use Tester\Assert;

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");


Assert::same(
	'SELECT TOP 2 * FROM [author]',
	$context->table('author')->limit(2)->getSql()
);

Assert::exception(function() use ($context) {
	$context->table('author')->limit(2, 10)->getSql();
}, 'Nette\NotSupportedException', 'Offset is not supported by this database.');

Assert::same(
	'SELECT TOP 2 * FROM [author] ORDER BY [name]',
	$context->table('author')->order('name')->limit(2)->getSql()
);

Assert::same(
	'SELECT TOP 10 * FROM [author]',
	$context->table('author')->page(1, 10)->getSql()
);

Assert::same(
	'SELECT TOP 10 * FROM [author]',
	$context->table('author')->page(0, 10)->getSql()
);

Assert::exception(function() use ($context) {
	$context->table('author')->page(2, 10, $count)->getSql();
}, 'Nette\NotSupportedException', 'Offset is not supported by this database.');

Assert::exception(function() use ($context) {
	$context->table('author')->page(2, 2, $count)->getSql();
}, 'Nette\NotSupportedException', 'Offset is not supported by this database.');
