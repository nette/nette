<?php

/**
 * Test: Nette\Database\Table: Calling __toString().
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");


test(function() use ($context) {
	Assert::same('2', (string) $context->table('book')->get(2));
});


test(function() use ($context) {
	Assert::same(2, $context->table('book')->get(2)->getPrimary());
});
