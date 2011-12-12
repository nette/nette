<?php

/**
 * Test: Nette\Database\Table: Subqueries.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once __DIR__ . '/connect.inc.php';



$apps = array();
$unknownBorn = $connection->table('author')->where('born', null); // authors with unknown date of born
foreach ($connection->table('book')->where('author_id', $unknownBorn) as $book) { // their books
	$apps[] = $book->title;
}

Assert::equal(array(
	'1001 tipu a triku pro PHP',
	'JUSH',
	'Nette',
	'Dibi',
), $apps);
