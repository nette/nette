<?php

/**
 * Test: Nette\Database\Table: Special case of caching
 *
 * @author     Jachym Tousek
 * @dataProvider? databases.ini
*/

use Tester\Assert;

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");


$cacheStorage = new Nette\Caching\Storages\MemoryStorage;
$connection->setCacheStorage($cacheStorage);
$connection->setDatabaseReflection(new Nette\Database\Reflection\DiscoveredReflection($cacheStorage));

$res = array();

for ($i = 1; $i <= 2; ++$i) {

	foreach ($connection->table('author') as $author) {
		$res[] = (string) $author->name;
		foreach ($author->related('book', 'author_id') as $book) {
			$res[] = (string) $book->title;
		}
	}

	foreach ($connection->table('author')->where('id', 13) as $author) {
		$res[] = (string) $author->name;
		foreach ($author->related('book', 'author_id') as $book) {
			$res[] = (string) $book->title;
		}
	}

}

Assert::same(array(
	'Jakub Vrana',
	'1001 tipu a triku pro PHP',
	'JUSH',
	'David Grudl',
	'Nette',
	'Dibi',
	'Geek',
	'Geek',
	'Jakub Vrana',
	'1001 tipu a triku pro PHP',
	'JUSH',
	'David Grudl',
	'Nette',
	'Dibi',
	'Geek',
	'Geek',
), $res);
