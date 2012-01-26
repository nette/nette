<?php

/**
 * Test: Nette\Database\Table: Basic operations.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once __DIR__ . '/connect.inc.php';



$book = $connection->table('book')->where('id = ?', 1)->select('id, title')->fetch()->toArray();  // SELECT `id`, `title` FROM `book` WHERE (`id` = ?)
Assert::same(array(
	'id' => 1,
	'title' => '1001 tipu a triku pro PHP',
), $book);

$book = $connection->table('book')->select('id, title')->where('id = ?', 1)->fetch()->toArray();  // SELECT `id`, `title` FROM `book` WHERE (`id` = ?)
Assert::same(array(
	'id' => 1,
	'title' => '1001 tipu a triku pro PHP',
), $book);



$appTags = array();
foreach ($connection->table('book') as $book) {  // SELECT * FROM `book`
	$appTags[$book->title] = array(
		'author' => $book->author->name,  // SELECT * FROM `author` WHERE (`author`.`id` IN (11, 12))
		'tags' => array(),
	);

	foreach ($book->related('book_tag') as $book_tag) {  // SELECT * FROM `book_tag` WHERE (`book_tag`.`book_id` IN (1, 2, 3, 4))
		$appTags[$book->title]['tags'][] = $book_tag->tag->name;  // SELECT * FROM `tag` WHERE (`tag`.`id` IN (21, 22, 23))
	}
}

Assert::same(array(
	'1001 tipu a triku pro PHP' => array(
		'author' => 'Jakub Vrana',
		'tags' => array('PHP', 'MySQL'),
	),
	'JUSH' => array(
		'author' => 'Jakub Vrana',
		'tags' => array('JavaScript'),
	),
	'Nette' => array(
		'author' => 'David Grudl',
		'tags' => array('PHP'),
	),
	'Dibi' => array(
		'author' => 'David Grudl',
		'tags' => array('PHP', 'MySQL'),
	),
), $appTags);



$sql = $connection->table('book')
	->where(new Nette\Database\SqlLiteral('id = 1'))
	->where(new Nette\Database\SqlLiteral('id = 1'))
	->getSql();

Assert::same('SELECT * FROM `book` WHERE (`id` = 1)', $sql);
