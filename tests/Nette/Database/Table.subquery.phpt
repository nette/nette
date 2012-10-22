<?php

/**
 * Test: Nette\Database\Table: Subqueries.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @multiple   databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");



$apps = array();
$unknownBorn = $connection->table('author')->where('born', null); // authors with unknown date of born
foreach ($connection->table('book')->where('author_id', $unknownBorn) as $book) { // their books: SELECT `id` FROM `author` WHERE (`born` IS NULL), SELECT * FROM `book` WHERE (`author_id` IN (11, 12))
	$apps[] = $book->title;
}

Assert::same(array(
	'1001 tipu a triku pro PHP',
	'JUSH',
	'Nette',
	'Dibi',
), $apps);


Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test4.sql");

$users = array();
$subquery = $connection->table('nUsers')->select('users_id, MAX(version), third')->group('users_id');
foreach ($connection->table('nUsers')->where('users_id,version,third',$subquery)->where('status = "u"') as $user) {
	$users[] = $user->name;
}

Assert::same(array(
	'Johnny',
	'Smith',
), $users);
