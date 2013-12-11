<?php

/**
 * Test: Nette\Database\Table: Cache observer.
 *
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

use Tester\Assert;
use Nette\Caching\Storages\MemoryStorage;

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");


class CacheMock extends MemoryStorage
{
	public $writes = 0;

	function write($key, $data, array $dependencies)
	{
		$this->writes++;
		return parent::write($key, $data, $dependencies);
	}
}

$dao = new Nette\Database\Context(
	$connection,
	new Nette\Database\Reflection\DiscoveredReflection($connection, new Nette\Caching\Storages\MemoryStorage),
	($cacheStorage = new CacheMock)
);


$authors = $dao->table('author');
foreach ($authors as $author) {
	$author->name;
}


$authors->where('web IS NOT NULL');
foreach ($authors as $author) {
	$author->web;
}

$authors->__destruct();


$authors = $dao->table('author');
Assert::equal(reformat('SELECT [id], [name] FROM [author]'), $authors->getSql());


Assert::same(2, $cacheStorage->writes);
