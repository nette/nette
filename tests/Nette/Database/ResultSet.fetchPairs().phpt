<?php

/**
 * Test: Nette\Database\ResultSet: Fetch pairs.
 *
 * @author     David Grudl
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");



test(function() use ($connection) {
	$res = $connection->query('SELECT * FROM book ORDER BY title');
	Assert::same(array(
		1 => '1001 tipu a triku pro PHP',
		4 => 'Dibi',
		2 => 'JUSH',
		3 => 'Nette',
	), $res->fetchPairs('id', 'title'));

	Assert::same(array(
		'1001 tipu a triku pro PHP' => 1,
		'Dibi' => 4,
		'JUSH' => 2,
		'Nette' => 3,
	), $res->fetchPairs('title', 'id'));
});



test(function() use ($connection) {
	$pairs = $connection->query('SELECT title, id FROM book ORDER BY title')->fetchPairs(1, 0);
	Assert::same(array(
		1 => '1001 tipu a triku pro PHP',
		4 => 'Dibi',
		2 => 'JUSH',
		3 => 'Nette',
	), $pairs);
});



test(function() use ($connection) {
	$pairs = $connection->query('SELECT * FROM book ORDER BY id')->fetchPairs('id', 'id');
	Assert::same(array(
		1 => 1,
		2 => 2,
		3 => 3,
		4 => 4,
	), $pairs);
});



test(function() use ($connection) {
	$pairs = $connection->query('SELECT id FROM book ORDER BY id')->fetchPairs('id');
	Assert::equal(array(
		1 => Nette\Database\Row::from(array('id' => 1)),
		2 => Nette\Database\Row::from(array('id' => 2)),
		3 => Nette\Database\Row::from(array('id' => 3)),
		4 => Nette\Database\Row::from(array('id' => 4)),
	), $pairs);
});



test(function() use ($connection) {
	$pairs = $connection->query('UPDATE author SET born = ? WHERE id = 11', new DateTime('2002-02-20'));
	$pairs = $connection->query('UPDATE author SET born = ? WHERE id = 12', new DateTime('2002-02-02'));
	$pairs = $connection->query('SELECT * FROM author WHERE born IS NOT NULL ORDER BY born')->fetchPairs('born', 'name');
	Assert::same(array(
		'2002-02-02 00:00:00' => 'David Grudl',
		'2002-02-20 00:00:00' => 'Jakub Vrana',
	), $pairs);
});
