<?php

/**
 * Test: Nette\Database\Table: Basic operations.
 *
 * @author     David Grudl
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . '/nette_test2.sql');
$connection->setDatabaseReflection(new Nette\Database\Reflection\DiscoveredReflection);



$names = array();
foreach ($connection->table('topics')->order('id') as $topic) {
	$names[] = $topic->user->name;
}

Assert::same(array(
	'Doe',
	'Doe',
	'John',
), $names);
