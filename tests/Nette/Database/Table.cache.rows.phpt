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
$connection->setSelectionFactory(new Nette\Database\Table\SelectionFactory(
	$connection,
	new Nette\Database\Reflection\DiscoveredReflection($connection, $cacheStorage),
	$cacheStorage
));



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
$books[] = $bookSelection->fetch();
$books[] = $bookSelection->fetch()->toArray();
$books[] = $bookSelection->fetch()->toArray();
Assert::equal(1, $books[0]['id']);
Assert::equal(2, $books[1]['id']);
Assert::equal(3, $books[2]['id']);



$row = $connection->table('author')->insert(array(
	'name' => 'Eddard Stark',
	'web' => 'http://example.com',
));  // INSERT INTO `author` (`name`, `web`) VALUES ('Eddard Stark', 'http://example.com')
Assert::true(is_array($row->toArray()));
// id = 14


$row = $connection->table('author')->where('id', 14)->fetch();
Assert::true(is_array($row->toArray()));
