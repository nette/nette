<?php

/**
 * Test: Nette\Database\ResultSet: Fetch all.
 *
 * @author     David Grudl
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");



$res = $connection->query('SELECT id FROM book ORDER BY id');

// sqlsrv: for real row count, PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL must be used when $pdo->prepare(). Nette\Database doesn't allowes it now.
Assert::same($driverName === 'sqlsrv' ? -1 : 4, $res->rowCount);
Assert::same(1, $res->columnCount);
Assert::same('SELECT id FROM book ORDER BY id', $res->getQueryString());

Assert::equal(array(
	Nette\Database\Row::from(array('id' => 1)),
	Nette\Database\Row::from(array('id' => 2)),
	Nette\Database\Row::from(array('id' => 3)),
	Nette\Database\Row::from(array('id' => 4)),
), $res->fetchAll());

Assert::equal(array(
	Nette\Database\Row::from(array('id' => 1)),
	Nette\Database\Row::from(array('id' => 2)),
	Nette\Database\Row::from(array('id' => 3)),
	Nette\Database\Row::from(array('id' => 4)),
), $res->fetchAll());
