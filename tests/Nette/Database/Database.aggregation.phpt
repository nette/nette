<?php

/**
 * Test: Nette\Database Aggregation functions.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once dirname(__FILE__) . '/connect.inc.php';



$count = $connection->table('book')->count('*');
Assert::equal(4, $count);

$tags = array();
foreach ($connection->table('book') as $book) {
	$count = $book->related('book_tag')->count('*');
	$tags[$book->title] = $count;
}

Assert::equal(array(
	'1001 tipu a triku pro PHP' => 2,
	'JUSH' => 1,
	'Nette' => 1,
	'Dibi' => 2,
), $tags);
