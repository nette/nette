<?php

/**
 * Test: Nette\Database\Table\Selection: Insert operations
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

use Tester\Assert;

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");


$book = $connection->table('author')->insert(array(
	'name' => $connection->literal('LOWER(?)', 'Eddard Stark'),
	'web' => 'http://example.com',
	'born' => new \DateTime('2011-11-11'),
));  // INSERT INTO `author` (`name`, `web`) VALUES (LOWER('Eddard Stark'), 'http://example.com', '2011-11-11 00:00:00')
// id = 14

Assert::equal('eddard stark', $book->name);
Assert::equal(new Nette\DateTime('2011-11-11'), $book->born);



$books = $connection->table('book');

$book1 = $books->get(1);  // SELECT * FROM `book` WHERE (`id` = ?)
Assert::same('Jakub Vrana', $book1->author->name);  // SELECT * FROM `author` WHERE (`author`.`id` IN (11))

$book2 = $books->insert(array(
	'title' => 'Dragonstone',
	'author_id' => $connection->table('author')->get(14),  // SELECT * FROM `author` WHERE (`id` = ?)
));  // INSERT INTO `book` (`title`, `author_id`) VALUES ('Dragonstone', 14)

Assert::same('eddard stark', $book2->author->name);  // SELECT * FROM `author` WHERE (`author`.`id` IN (11, 15))



// SQL Server throw PDOException because does not allow insert explicit value for IDENTITY column.
// This exception is about primary key violation.
if ($driverName !== 'sqlsrv') {
	Assert::exception(function() use ($connection) {
		$connection->table('author')->insert(array(
			'id' => 14,
			'name' => 'Jon Snow',
			'web' => 'http://example.com',
		));
	}, '\PDOException');
}



switch ($driverName) {
	case 'mysql':
		$selection = $connection->table('author')->select('NULL, id, NULL, CONCAT(?, name), NULL',  'Biography: ');
		break;
	case 'pgsql':
		$selection = $connection->table('author')->select('nextval(?), id, NULL, ? || name, NULL', 'book_id_seq', 'Biography: ');
		break;
	case 'sqlite':
		$selection = $connection->table('author')->select('NULL, id, NULL, ? || name, NULL', 'Biography: ');
		break;
	case 'sqlsrv':
		$selection = $connection->table('author')->select('id, NULL, CONCAT(?, name), NULL', 'Biography: ');
		break;
	default:
		Assert::fail("Unsupported driver $driverName");
}
$connection->table('book')->insert($selection);
Assert::equal(4, $connection->table('book')->where('title LIKE', "Biography%")->count('*'));



// Insert into table without primary key
$connection->setSelectionFactory(new Nette\Database\Table\SelectionFactory(
	$connection,
	new Nette\Database\Reflection\DiscoveredReflection($connection)
));

$inserted = $connection->table('note')->insert(array(
	'book_id' => 1,
	'note' => 'Good one!',
));
Assert::equal(1, $inserted);
