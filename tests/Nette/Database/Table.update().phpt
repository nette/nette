<?php

/**
 * Test: Nette\Database\Table: Update operations
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @multiple   databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");



$author = $connection->table('author')->get(12);  // SELECT * FROM `author` WHERE (`id` = ?)
$author->name = 'Tyrion Lannister';
$author->update();  // UPDATE `author` SET `name`='Tyrion Lannister' WHERE (`id` = 12)

$book = $connection->table('book');

$book1 = $book->get(1);  // SELECT * FROM `book` WHERE (`id` = ?)
Assert::same('Jakub Vrana', $book1->author->name);  // SELECT * FROM `author` WHERE (`author`.`id` IN (11))



$book2 = $book->insert(array(
	'author_id' => $author->getPrimary(),
	'title' => 'Game of Thrones',
));  // INSERT INTO `book` (`author_id`, `title`) VALUES (12, 'Game of Thrones')

Assert::same('Tyrion Lannister', $book2->author->name);  // SELECT * FROM `author` WHERE (`author`.`id` IN (12))



$book2->author_id = $connection->table('author')->get(12);  // SELECT * FROM `author` WHERE (`id` = ?)
$book2->update();  // UPDATE `book` SET `author_id`=11 WHERE (`id` = '5')

Assert::same('Tyrion Lannister', $book2->author->name);  // NO SQL, SHOULD BE CACHED

$book2->author_id = $connection->table('author')->get(11);  // SELECT * FROM `author` WHERE (`id` = ?)
$book2->update();  // UPDATE `book` SET `author_id`=11 WHERE (`id` = '5')

Assert::same('Jakub Vrana', $book2->author->name);  // SELECT * FROM `author` WHERE (`author`.`id` IN (11))




$tag = $connection->table('tag')->insert(array(
	'name' => 'PC Game',
));  // INSERT INTO `tag` (`name`) VALUES ('PC Game')

$tag->name = 'Xbox Game';
$tag->update();  // UPDATE `tag` SET `name`='Xbox Game' WHERE (`id` = '24')


$bookTag = $book2->related('book_tag')->insert(array(
	'tag_id' => $tag,
));  // INSERT INTO `book_tag` (`tag_id`, `book_id`) VALUES (24, '5')


$app = $connection->table('book')->get(5);  // SELECT * FROM `book` WHERE (`id` = ?)
$tags = iterator_to_array($app->related('book_tag'));  // SELECT * FROM `book_tag` WHERE (`book_tag`.`book_id` IN (5))
Assert::same('Xbox Game', reset($tags)->tag->name);  // SELECT * FROM `tag` WHERE (`tag`.`id` IN (24))
