<?php

/**
 * Test: Nette\Database\Connection fetch methods.
 *
 * @author     David Grudl
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");



// fetch
$row = $connection->fetch('SELECT name, id FROM author WHERE id = ?', 11);
Assert::true($row instanceof Nette\Database\Row);
Assert::equal(Nette\Database\Row::from(array(
	'name' => 'Jakub Vrana',
	'id' => 11,
)), $row);


// fetchField
Assert::same('Jakub Vrana', $connection->fetchField('SELECT name FROM author ORDER BY id'));


// fetchPairs
$pairs = $connection->fetchPairs('SELECT name, id FROM author WHERE id > ? ORDER BY id', 11);
Assert::same(array(
	'David Grudl' => 12,
	'Geek' => 13,
), $pairs);


// fetchAll
$arr = $connection->fetchAll('SELECT name, id FROM author WHERE id < ? ORDER BY id', 13);
Assert::equal(array(
	Nette\Database\Row::from(array('name' => 'Jakub Vrana', 'id' => 11)),
	Nette\Database\Row::from(array('name' => 'David Grudl', 'id' => 12)),
), $arr);
