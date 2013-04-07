<?php

/**
 * Test: Nette\Database\Connection fetch methods.
 *
 * @author     David Grudl
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");



// fetch
$row = $connection->fetch('SELECT name, id FROM author WHERE id = ?', 11);
Assert::true($row instanceof Nette\Database\Row);
Assert::same(array(
	'name' => 'Jakub Vrana',
	'id' => 11,
), (array) $row);


// fetchColumn
Assert::same('Jakub Vrana', $connection->fetchColumn('SELECT name FROM author ORDER BY id'));


// fetchPairs
$pairs = $connection->fetchPairs('SELECT id, name FROM author WHERE id > ? ORDER BY id', 11);
Assert::equal(array(
	12 => 'David Grudl',
	13 => 'Geek',
), $pairs);


// fetchAll
$arr = $connection->fetchAll('SELECT name, id FROM author WHERE id < ? ORDER BY id', 13);
foreach ($arr as &$row) {
	Assert::true($row instanceof Nette\Database\Row);
	$row = (array) $row;
}
Assert::equal(array(
	array('name' => 'Jakub Vrana', 'id' => 11),
	array('name' => 'David Grudl', 'id' => 12),
), $arr);
