<?php

/**
 * Test: Nette\Database\Table: Rows invalidating.
 *
 * @author     Jachym Tousek
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



$entity = $connection->table('book')->get(1);
$entity->author->getTable()->__destruct();
$entity = $connection->table('book')->get(1);
$entity->author->name = 'David Grudl';
isset($entity->author->nonexistent);
Assert::same('David Grudl', $entity->author->name);
