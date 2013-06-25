<?php

/**
 * Test: Nette\Database\Connection transaction methods.
 *
 * @author     David Grudl
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");


test(function() use ($connection) {
	$connection->beginTransaction();
	$connection->query('DELETE FROM book');
	$connection->rollBack();

	Assert::same( 3, $connection->fetchField('SELECT id FROM book WHERE id = ', 3) );
});



test(function() use ($connection) {
	$connection->beginTransaction();
	$connection->query('DELETE FROM book');
	$connection->commit();

	Assert::same( FALSE, $connection->fetchField('SELECT id FROM book WHERE id = ', 3) );
});
