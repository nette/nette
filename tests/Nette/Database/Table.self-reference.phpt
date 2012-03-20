<?php

/**
 * Test: Nette\Database\Table: DiscoveredReflection with self-reference.
 *
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

use Nette\Database;



require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . '/nette_test1.sql');
$connection->setDatabaseReflection(new Database\Reflection\DiscoveredReflection);



$connection->query('ALTER TABLE `book` ADD COLUMN `next_volume` int NULL AFTER `title`;');
$connection->query('ALTER TABLE `book` ADD CONSTRAINT `book_volume` FOREIGN KEY (`next_volume`) REFERENCES `book` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;');
$connection->query('UPDATE `book` SET `next_volume` = 3 WHERE `id` IN (2,4)');

$book = $connection->table('book')->get(4);
Assert::same('Nette', $book->volume->title);
Assert::same('Nette', $book->ref('book', 'next_volume')->title);



$book = $connection->table('book')->get(3);
Assert::same(2, $book->related('book.next_volume')->count('*'));
Assert::same(2, $book->related('book', 'next_volume')->count('*'));
