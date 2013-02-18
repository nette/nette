<?php

/**
 * Test: Nette\Database\Table: Insert operations
 *
 * @author     Petr Peller
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



$row = $connection->table('author')->insert(array(
	'id' => 14,
	'name' => 'Eddard Stark',
	'web' => 'http://example.com',
));  // INSERT INTO `author` (`id`, `name`, `web`) VALUES (14, 'Edard Stark', 'http://example.com')
Assert::true(is_array($row->toArray()));


$row = $connection->table('author')->where('id', 14)->fetch();
Assert::true(is_array($row->toArray()));

