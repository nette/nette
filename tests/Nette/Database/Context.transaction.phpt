<?php

/**
 * Test: Nette\Database\Connection transaction methods.
 * @dataProvider? databases.ini
 */

use Tester\Assert;

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");


test(function () use ($context) {
	$context->beginTransaction();
	$context->query('DELETE FROM book');
	$context->rollBack();

	Assert::same(3, $context->fetchField('SELECT id FROM book WHERE id = ', 3));
});


test(function () use ($context) {
	$context->beginTransaction();
	$context->query('DELETE FROM book');
	$context->commit();

	Assert::false($context->fetchField('SELECT id FROM book WHERE id = ', 3));
});
