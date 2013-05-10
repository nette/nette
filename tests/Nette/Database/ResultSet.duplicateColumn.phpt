<?php

/**
 * Test: Nette\Database\ResultSet duplicated column
 *
 * @author     David Grudl
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");



$res = $connection->query('SELECT name, name FROM author');

Assert::error(function () use ($res) {
	$res->fetch();
}, E_USER_NOTICE, 'Found duplicate columns in database result set.');

$res->fetch();
