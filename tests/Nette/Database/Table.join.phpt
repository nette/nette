<?php

/**
 * Test: Nette\Database\Table: Join.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once __DIR__ . '/connect.inc.php';



$apps = array();
foreach ($connection->table('book')->order('author.name, title') as $book) {  // SELECT `book`.* FROM `book` LEFT JOIN `author` ON `book`.`author_id` = `author`.`id` ORDER BY `author`.`name`, `title`
	$apps[$book->title] = $book->author->name;  // SELECT * FROM `author` WHERE (`author`.`id` IN (12, 11))
}

Assert::same(array(
	'Dibi' => 'David Grudl',
	'Nette' => 'David Grudl',
	'1001 tipu a triku pro PHP' => 'Jakub Vrana',
	'JUSH' => 'Jakub Vrana',
), $apps);



$tags = array();
foreach ($connection->table('book_tag')->where('book.author.name', 'Jakub Vrana')->group('book_tag.tag_id') as $book_tag) {  // SELECT `book_tag`.* FROM `book_tag` INNER JOIN `book` ON `book_tag`.`book_id` = `book`.`id` INNER JOIN `author` ON `book`.`author_id` = `author`.`id` WHERE (`author`.`name` = ?) GROUP BY `book_tag`.`tag_id`
	$tags[] = $book_tag->tag->name;  // SELECT * FROM `tag` WHERE (`tag`.`id` IN (21, 22, 23))
}

Assert::same(array(
	'PHP',
	'MySQL',
	'JavaScript',
), $tags);



Assert::same(2, $connection->table('author')->where('author_id', 11)->count('book:id')); // SELECT COUNT(book.id) FROM `author` LEFT JOIN `book` ON `author`.`id` = `book`.`author_id` WHERE (`author_id` = 11)
