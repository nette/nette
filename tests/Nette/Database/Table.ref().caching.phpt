<?php

/**
 * Test: Nette\Database\Table: Caching.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @multiple   databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");



$cacheStorage = new Nette\Caching\Storages\MemoryStorage;
$connection->setCacheStorage($cacheStorage);
$connection->setDatabaseReflection(new Nette\Database\Reflection\DiscoveredReflection($cacheStorage));



// Testing Selection caching
$bookSelection = $connection->table('book')->find(2);
switch ($driverName) {
	case 'mysql':
		Assert::same('SELECT * FROM `book` WHERE (`id` = ?)', $bookSelection->getSql());
		break;
	case 'pgsql':
		Assert::same('SELECT * FROM "book" WHERE ("id" = ?)', $bookSelection->getSql());
		break;
}


$book = $bookSelection->fetch();
$book->ref('author');
$bookSelection->__destruct();
$bookSelection = $connection->table('book')->find(2);
switch ($driverName) {
	case 'mysql':
		Assert::same('SELECT `id`, `author_id` FROM `book` WHERE (`id` = ?)', $bookSelection->getSql());
		break;
	case 'pgsql':
		Assert::same('SELECT "id", "author_id" FROM "book" WHERE ("id" = ?)', $bookSelection->getSql());
		break;
}


$book = $bookSelection->fetch();
$book->ref('author', 'translator_id');
$bookSelection->__destruct();
$bookSelection = $connection->table('book')->find(2);
switch ($driverName) {
	case 'mysql':
		Assert::same('SELECT `id`, `author_id`, `translator_id` FROM `book` WHERE (`id` = ?)', $bookSelection->getSql());
		break;
	case 'pgsql':
		Assert::same('SELECT "id", "author_id", "translator_id" FROM "book" WHERE ("id" = ?)', $bookSelection->getSql());
		break;
}
