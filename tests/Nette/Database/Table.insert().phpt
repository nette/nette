<?php

/**
 * Test: Nette\Database\Table: Insert operations
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");



$connection->table('author')->insert(array(
	'id' => 14,
	'name' => 'Eddard Stark',
	'web' => 'http://example.com',
));  // INSERT INTO `author` (`id`, `name`, `web`) VALUES (14, 'Edard Stark', 'http://example.com')

switch ($driverName) {
	case 'pgsql':
	$connection->exec("SELECT setval('author_id_seq'::regclass, 14, TRUE)");
	break;
}



$insert = array(
	'name' => 'Catelyn Stark',
	'web' => 'http://example.com',
	'born' => new Nette\DateTime('2011-11-11'),
);
$connection->table('author')->insert($insert);  // INSERT INTO `author` (`name`, `web`, `born`) VALUES ('Catelyn Stark', 'http://example.com', '2011-11-11 00:00:00')



$catelynStark = $connection->table('author')->get(15);  // SELECT * FROM `author` WHERE (`id` = ?)
Assert::equal(array(
	'id' => 15,
	'name' => 'Catelyn Stark',
	'web' => 'http://example.com',
	'born' => new Nette\DateTime('2011-11-11'),
), $catelynStark->toArray());



$book = $connection->table('book');

$book1 = $book->get(1);  // SELECT * FROM `book` WHERE (`id` = ?)
Assert::same('Jakub Vrana', $book1->author->name);  // SELECT * FROM `author` WHERE (`author`.`id` IN (11))

$book2 = $book->insert(array(
	'title' => 'Winterfell',
	'author_id' => 11,
));  // INSERT INTO `book` (`title`, `author_id`) VALUES ('Winterfell', 11)

Assert::same('Jakub Vrana', $book2->author->name);  // SELECT * FROM `author` WHERE (`author`.`id` IN (11, 15))

$book3 = $book->insert(array(
	'title' => 'Dragonstone',
	'author_id' => $connection->table('author')->get(14),  // SELECT * FROM `author` WHERE (`id` = ?)
));  // INSERT INTO `book` (`title`, `author_id`) VALUES ('Dragonstone', 14)

Assert::same('Eddard Stark', $book3->author->name);  // SELECT * FROM `author` WHERE (`author`.`id` IN (11, 15))



Assert::exception(function() use ($connection) {
	$connection->table('author')->insert(array(
		'id' => 15,
		'name' => 'Jon Snow',
		'web' => 'http://example.com',
	));
}, '\PDOException');
