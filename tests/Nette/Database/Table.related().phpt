<?php

/**
 * Test: Nette\Database\Table: Related().
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once __DIR__ . '/connect.inc.php';



$apps = array();
foreach ($connection->table('author') as $author) {  // SELECT * FROM `author`
	foreach ($author->related('book', 'translator_id') as $book) {  // SELECT * FROM `book` WHERE (`book`.`translator_id` IN (11, 12))
		$apps[$book->title] = $author->name;
	}

	foreach ($author->related('book.author_id') as $book) {  // SELECT * FROM `book` WHERE (`book`.`author_id` IN (11, 12))
		$apps2[$book->title] = $author->name;
	}
}

Assert::same(array(
	'1001 tipu a triku pro PHP' => 'Jakub Vrana',
	'Nette' => 'David Grudl',
	'Dibi' => 'David Grudl',
), $apps);

Assert::same(array(
	'1001 tipu a triku pro PHP' => 'Jakub Vrana',
	'JUSH' => 'Jakub Vrana',
	'Nette' => 'David Grudl',
	'Dibi' => 'David Grudl',
), $apps2);
