<?php

/**
 * Test: Nette\Database\Table: Calling __toString().
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");



test(function() use ($dao) {
	Assert::same('2', (string) $dao->table('book')->get(2));
});



test(function() use ($dao) {
	Assert::same(2, $dao->table('book')->get(2)->getPrimary());
});
