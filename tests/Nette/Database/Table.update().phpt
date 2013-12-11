<?php

/**
 * Test: Nette\Database\Table: Update operations
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

use Tester\Assert;

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");


$dao = new Nette\Database\Context(
	$connection,
	new Nette\Database\Reflection\DiscoveredReflection($connection)
);


$author = $dao->table('author')->get(12);  // SELECT * FROM `author` WHERE (`id` = ?)
$author->update(array(
	'name' => 'Tyrion Lannister',
));  // UPDATE `author` SET `name`='Tyrion Lannister' WHERE (`id` = 12)

$book = $dao->table('book');

$book1 = $book->get(1);  // SELECT * FROM `book` WHERE (`id` = ?)
Assert::same('Jakub Vrana', $book1->author->name);  // SELECT * FROM `author` WHERE (`author`.`id` IN (11))


$book2 = $book->insert(array(
	'author_id' => $author->getPrimary(),
	'title' => 'Game of Thrones',
));  // INSERT INTO `book` (`author_id`, `title`) VALUES (12, 'Game of Thrones')

Assert::same('Tyrion Lannister', $book2->author->name);  // SELECT * FROM `author` WHERE (`author`.`id` IN (12))


$book2->update(array(
	'author_id' => $dao->table('author')->get(12),  // SELECT * FROM `author` WHERE (`id` = ?)
));  // UPDATE `book` SET `author_id`=11 WHERE (`id` = '5')

Assert::same('Tyrion Lannister', $book2->author->name);  // NO SQL, SHOULD BE CACHED

$book2->update(array(
	'author_id' => $dao->table('author')->get(11),  // SELECT * FROM `author` WHERE (`id` = ?)
));  // UPDATE `book` SET `author_id`=11 WHERE (`id` = '5')

Assert::same('Jakub Vrana', $book2->author->name);  // SELECT * FROM `author` WHERE (`author`.`id` IN (11))

$book2->update(array(
	'author_id' => new Nette\Database\SqlLiteral('10 + 3'),
));  // UPDATE `book` SET `author_id`=13 WHERE (`id` = '5')

Assert::same('Geek', $book2->author->name);  // SELECT * FROM `author` WHERE (`author`.`id` IN (13))
Assert::same(13, $book2->author_id);


$tag = $dao->table('tag')->insert(array(
	'name' => 'PC Game',
));  // INSERT INTO `tag` (`name`) VALUES ('PC Game')

$tag->update(array(
	'name' => 'Xbox Game',
));  // UPDATE `tag` SET `name`='Xbox Game' WHERE (`id` = '24')


$bookTag = $book2->related('book_tag')->insert(array(
	'tag_id' => $tag,
));  // INSERT INTO `book_tag` (`tag_id`, `book_id`) VALUES (24, '5')


$app = $dao->table('book')->get(5);  // SELECT * FROM `book` WHERE (`id` = ?)
$tags = iterator_to_array($app->related('book_tag'));  // SELECT * FROM `book_tag` WHERE (`book_tag`.`book_id` IN (5))
Assert::same('Xbox Game', reset($tags)->tag->name);  // SELECT * FROM `tag` WHERE (`tag`.`id` IN (24))
