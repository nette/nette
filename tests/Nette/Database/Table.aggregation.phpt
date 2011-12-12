<?php

/**
 * Test: Nette\Database\Table: Aggregation functions.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once __DIR__ . '/connect.inc.php';



$count = $connection->table('book')->count('*');  // SELECT COUNT(*) FROM `book`
Assert::equal(4, $count);

$tags = array();
foreach ($connection->table('book') as $book) {  // SELECT * FROM `book`
	$count = $book->related('book_tag')->count('*');  // SELECT COUNT(*), `book_id` FROM `book_tag` WHERE (`book_tag`.`book_id` IN (1, 2, 3, 4)) GROUP BY `book_id`
	$tags[$book->title] = $count;
}

Assert::equal(array(
	'1001 tipu a triku pro PHP' => 2,
	'JUSH' => 1,
	'Nette' => 1,
	'Dibi' => 2,
), $tags);
