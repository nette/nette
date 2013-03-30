<?php

/**
 * Test: Nette\Database\Statement: Fetch all.
 *
 * @author     David Grudl
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");



$res = $connection->query('SELECT id FROM book ORDER BY id');

Assert::same(4, $res->rowCount);
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
