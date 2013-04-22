<?php

/**
 * Test: Nette\Database\Table: Cache observer.
 *
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

use Nette\Database\ResultSet;

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");

class CacheMock implements Nette\Caching\IStorage
{
	public $writes = 0;
	private $defaultBook = array('id' => TRUE, 'author_id' => TRUE);

	function read($key)
	{
		$key = substr($key, strpos($key, "\x00") + 1);
		switch ($key) {
			case "aad5184d8c52b773bd73b5c7c5c819c9": // authors
				return array('id' => TRUE);
			case "d7dc896279409ab73e6742c667cf8dc1": // book
				return $this->defaultBook;
		}
	}

	function write($key, $data, array $dependencies)
	{
		$key = substr($key, strpos($key, "\x00") + 1);
		$this->writes++;
		switch ($key) {
			case "aad5184d8c52b773bd73b5c7c5c819c9":
				return;
			case "d7dc896279409ab73e6742c667cf8dc1":
				$this->defaultBook = $data;
				return;
		}
	}

	function lock($key) {}
	function remove($key) {}
	function clean(array $conditions) {}
}

$cacheStorage = new CacheMock;
$connection->setSelectionFactory(new Nette\Database\SelectionFactory(
	$connection,
	new Nette\Database\Reflection\DiscoveredReflection($connection, new Nette\Caching\Storages\MemoryStorage),
	$cacheStorage
));



$queries = 0;
$connection->onQuery[] = function($connection, ResultSet $result) use (& $queries) {
	if (!preg_match('#SHOW|CONSTRAINT_NAME|pg_catalog|sys\.|SET|PRAGMA|FROM sqlite_#i', $result->queryString)) {
		$queries++;
	}
};

$authors = $connection->table('author');
$stack = array();
foreach ($authors as $author) {
	foreach ($stack[] = $author->related('book') as $book) {
		$book->title;
	}
}

unset($book, $author);
foreach ($stack as $selection) $selection->__destruct();
$authors->__destruct();

Assert::same(1, $cacheStorage->writes);
Assert::same(3, $queries);
