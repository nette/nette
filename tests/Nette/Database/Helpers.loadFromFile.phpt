<?php

/**
 * Test: Nette\Database\Helpers::loadFromFile().
 *
 * @author     David Grudl
 * @dataProvider? databases.ini  mysql
 */

use Tester\Assert;

require __DIR__ . '/connect.inc.php'; // create $connection


Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/mysql-delimiter.sql");

$arr = $connection->query('SELECT name, id FROM author ORDER BY id')->fetchAll();
Assert::equal(array(
	Nette\Database\Row::from(array('name' => 'Jakub Vrana', 'id' => 11)),
	Nette\Database\Row::from(array('name' => 'David Grudl', 'id' => 12)),
), $arr);
