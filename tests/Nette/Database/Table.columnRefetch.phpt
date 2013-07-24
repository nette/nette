<?php

/**
 * Test: Nette\Database\Table: Refetching rows with all columns
 *
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");


$cacheStorage = new Nette\Caching\Storages\MemoryStorage;
$dao = new Nette\Database\SelectionFactory(
	$connection,
	new Nette\Database\Reflection\DiscoveredReflection($connection, $cacheStorage),
	$cacheStorage
);


$books = $dao->table('book')->order('id DESC')->limit(2);
foreach ($books as $book) {
	$book->title;
}
$books->__destruct();


$books = $dao->table('book')->order('id DESC')->limit(2);
foreach ($books as $book) {
	$book->title;
}

$dao->table('book')->insert(array(
	'title' => 'New book #1',
	'author_id' => 11,
));
$dao->table('book')->insert(array(
	'title' => 'New book #2',
	'author_id' => 11,
));

foreach ($books as $book) {
	$book->title;
	$book->author->name;
}
