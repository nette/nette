<?php

/**
 * Test: Nette\Database\Table: Delete operations
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once __DIR__ . '/connect.inc.php';



$connection->table('book_tag')->where('book_id', 4)->delete();

$count = $connection->table('book_tag')->where('book_id', 4)->count();
Assert::equal(0, $count);



$book = $connection->table('book')->get(3);
$book->related('book_tag')->delete();

$count = $connection->table('book_tag')->where('book_id', 3)->count();
Assert::equal(0, $count);



$book->delete();
Assert::equal(0, count($connection->table('book')->find(3)));
