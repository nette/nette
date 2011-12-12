<?php

/**
 * Test: Nette\Database\Table: Find one item by URL.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once __DIR__ . '/connect.inc.php';



$tags = array();
$book = $connection->table('book')->where('title', '1001 tipu a triku pro PHP')->fetch();
foreach ($book->related('book_tag')->where('tag_id', 21) as $book_tag) {
	$tags[] = $book_tag->tag->name;
}

Assert::equal(array('PHP'), $tags);
