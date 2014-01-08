<?php

/**
 * Test: Nette\Database\Table: Refetching rows with all columns
 *
 * @author     Jan Skrasek
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");


$cacheStorage = new Nette\Caching\Storages\MemoryStorage;
$context = new Nette\Database\Context(
	$connection,
	new Nette\Database\Reflection\DiscoveredReflection($connection, $cacheStorage),
	$cacheStorage
);


$books = $context->table('book')->order('id DESC')->limit(2);
foreach ($books as $book) {
	$book->title;
}
$books->__destruct();


$books = $context->table('book')->order('id DESC')->limit(2);
foreach ($books as $book) {
	$book->title;
}

$context->table('book')->insert(array(
	'title' => 'New book #1',
	'author_id' => 11,
));
$context->table('book')->insert(array(
	'title' => 'New book #2',
	'author_id' => 11,
));

foreach ($books as $book) {
	$book->title;
	$book->author->name;
}
