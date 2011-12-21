<?php

/**
 * Test: Nette\Database\Table: Reference ref().
 *
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once __DIR__ . '/connect.inc.php';



Assert::same('Jakub Vrana', $connection->table('book')->get(1)->ref('author')->name);



$book = $connection->table('book')->get(1);
$book->translator_id = 12;
$book->update();
Assert::same('David Grudl', $connection->table('book')->get(1)->ref('author', 'translator_id')->name);



Assert::null($connection->table('book')->get(2)->ref('author', 'translator_id'));
