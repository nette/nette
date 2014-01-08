<?php

/**
 * Test: Nette\Database\Table: Cache observer.
 *
 * @author     Jan Skrasek
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;
use Nette\Caching\Storages\MemoryStorage;

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");


class CacheMock extends MemoryStorage
{
	public $writes = 0;

	function write($key, $data, array $dependencies)
	{
		$this->writes++;
		return parent::write($key, $data, $dependencies);
	}
}

$cacheStorage = new CacheMock;
$context = new Nette\Database\Context($context->getConnection(), $context->getDatabaseReflection(), $cacheStorage);


$authors = $context->table('author');
foreach ($authors as $author) {
	$author->name;
}


$authors->where('web IS NOT NULL');
foreach ($authors as $author) {
	$author->web;
}

$authors->__destruct();


$authors = $context->table('author');
Assert::equal(reformat('SELECT [id], [name] FROM [author]'), $authors->getSql());


Assert::same(2, $cacheStorage->writes);
