<?php

/**
 * Test: Nette\Database\Table: Special case of caching
 *
 * @author     Jachym Tousek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");



$cacheStorage = new Nette\Caching\Storages\MemoryStorage;
$connection->setSelectionFactory(new Nette\Database\Table\SelectionFactory(
	$connection,
	new Nette\Database\Reflection\DiscoveredReflection($connection, $cacheStorage),
	$cacheStorage
));



for ($i = 1; $i <= 2; ++$i) {

	foreach ($connection->table('author') as $author) {
		$author->name;
		foreach ($author->related('book', 'author_id') as $book) {
			$book->title;
		}
	}

	foreach ($connection->table('author')->where('id', 13) as $author) {
		$author->name;
		foreach ($author->related('book', 'author_id') as $book) {
			$book->title;
		}
	}

}
