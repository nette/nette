<?php

/**
 * Test: Nette\Database\Connection: static reflection
 *
 * @author     Jan Dolecek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . '/nette_test1.sql');



$neon = Nette\Utils\Neon::decode(file_get_contents(__DIR__ . '/StaticReflection.neon'));
$reflection = new Nette\Database\Reflection\StaticReflection($neon['database']);
$connection->setDatabaseReflection($reflection);



Assert::same( 'id', $reflection->getPrimary('author') );
Assert::same( NULL, $reflection->getPrimary('book_tag') );



$jakub = $connection->table('author')->get(11);
Assert::same( 'Jakub Vrana', $jakub->name );



$book = $jakub->related('book')->fetch();
Assert::same( 1, $book->id );
Assert::same( 'Jakub Vrana', $book->author->name );
Assert::same( 'Jakub Vrana', $book->translator->name );
