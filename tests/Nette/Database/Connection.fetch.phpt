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



test(function() use ($connection) { // fetch
	$row = $connection->fetch('SELECT name, id FROM author WHERE id = ?', 11);
	Assert::type( 'Nette\Database\Row', $row );
	Assert::equal(Nette\Database\Row::from(array(
		'name' => 'Jakub Vrana',
		'id' => 11,
	)), $row);
});



test(function() use ($connection) { // fetchField
	Assert::same('Jakub Vrana', $connection->fetchField('SELECT name FROM author ORDER BY id'));
});



test(function() use ($connection) { // fetchPairs
	$pairs = $connection->fetchPairs('SELECT name, id FROM author WHERE id > ? ORDER BY id', 11);
	Assert::same(array(
		'David Grudl' => 12,
		'Geek' => 13,
	), $pairs);
});



test(function() use ($connection) { // fetchAll
	$arr = $connection->fetchAll('SELECT name, id FROM author WHERE id < ? ORDER BY id', 13);
	Assert::equal(array(
		Nette\Database\Row::from(array('name' => 'Jakub Vrana', 'id' => 11)),
		Nette\Database\Row::from(array('name' => 'David Grudl', 'id' => 12)),
	), $arr);
});
