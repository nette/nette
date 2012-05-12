<?php

/**
 * Test: Nette\Database\Connection: chain static and MySQL reflection
 *
 * @author     Jan Dolecek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . '/nette_test1.sql');



$neon = Nette\Utils\Neon::decode(file_get_contents(__DIR__ . '/StaticReflection.neon'));
$reflection1 = new Nette\Database\Reflection\StaticReflection($neon['simple']);

$reflection2 = new Nette\Database\Reflection\DiscoveredReflection;
$reflection2->setConnection($connection);

$chain = new Nette\Database\Reflection\ReflectionChain;
$chain->addReflection($reflection1);
$chain->addReflection($reflection2);

$connection->setDatabaseReflection($chain);



$jakub = $connection->table('author')->get(11);
$book = $jakub->related('book')->fetch();
Assert::same( 1, $book->id );
Assert::same( 'Jakub Vrana', $book->author->name );  // uses DiscoveredReflection
Assert::same( 'Jakub Vrana', $book->author2->name ); // uses StaticReflection
Assert::same( 'Jakub Vrana', $book->translator->name );
