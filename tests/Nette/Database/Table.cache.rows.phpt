<?php

/**
 * Test: Nette\Database\Table: Rows invalidating.
 *
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");

$cacheStorage = new Nette\Caching\Storages\MemoryStorage;
$connection->setCacheStorage($cacheStorage);
$connection->setDatabaseReflection(new Nette\Database\Reflection\DiscoveredReflection($cacheStorage));



$selections = array();
foreach ($selections[] = $connection->table('book') as $book) {
	$book->author->name;
	$selections[] = $book->author->getTable();
}
foreach ($selections as $selection) {
	$selection->__destruct();
}

$authors = array();
foreach ($connection->table('book') as $book) {
	$authors[] = $book->author;
}

$webs = array();
foreach ($authors as $author) {
	$webs[$author->web] = NULL;
}
ksort($webs);
Assert::equal(array(
	'http://davidgrudl.com/',
	'http://www.vrana.cz/',
), array_keys($webs));



$bookSelection = $connection->table('book')->order('id');
$book = $bookSelection->fetch();
$book->author_id;
$bookSelection->__destruct();

$bookSelection = $connection->table('book')->order('id');
$books = array();
$books[] = $bookSelection->fetch()->toArray();
$books[] = $bookSelection->fetch()->toArray();
Assert::equal(1, $books[0]['id']);
Assert::equal(2, $books[1]['id']);
