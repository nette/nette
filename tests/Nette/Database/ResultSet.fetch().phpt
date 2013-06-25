<?php

/**
 * Test: Nette\Database\ResultSet::fetch()
 *
 * @author     David Grudl
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

use Tester\Assert;

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");



test(function() use ($connection) {
	$res = $connection->query('SELECT name, name FROM author');

	Assert::error(function () use ($res) {
		$res->fetch();
	}, E_USER_NOTICE, 'Found duplicate columns in database result set.');

	$res->fetch();
});



test(function() use ($connection, $driverName) { // tests closeCursor()
	if ($driverName === 'mysql') {
		$connection->query('CREATE DEFINER = CURRENT_USER PROCEDURE `testProc`(IN param int(10) unsigned) BEGIN SELECT * FROM book WHERE id != param; END;;');
		$connection->getPdo()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, FALSE);

		$res = $connection->query('CALL testProc(1)');
		foreach ($res as $row) {}

		$res = $connection->query('SELECT * FROM book');
		foreach ($res as $row) {}
	}
});
