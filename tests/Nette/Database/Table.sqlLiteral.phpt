<?php

/**
 * Test: Nette\Database\Table: Subqueries.
 *
 * @author     Caine
 * @package    Nette\Database
 * @multiple   databases.ini
 */

use Nette\Database\SqlLiteral;

require __DIR__ . '/connect.inc.php'; // create $connection
Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");



/* @var $connection \Nette\Database\Connection */
$selectLiteral = new SqlLiteral('DATE_FORMAT("2012-10-12", "%d.%m.%Y") as test_made');
$whereLiteral = new SqlLiteral('name LIKE "%david%"'); //for testing purpose only, for LIKE in code use "$selection->where('name LIKE ?', '%david%')" ;)
$groupLiteral = new SqlLiteral('author.id');
$havingLiteral = new SqlLiteral('books >= 1');
$orderLiteral = new SqlLiteral('exists(select * from book where book.translator_id = author.id)');
$orderLiteral2 = new SqlLiteral('EXISTS(SELECT * FROM book WHERE book.translator_id = author.id)');



//SqlLiteral in select
$selection = $connection->table('author')->select('name')->select($selectLiteral); //quite nonsense but its for testing purpose only;)
$sql = $selection->getSql();
Assert::same('SELECT `name`, DATE_FORMAT("2012-10-12", "%d.%m.%Y") as test_made FROM `author`', $sql);
Assert::same(array(
	'name' => 'Jakub Vrana',
	'test_made' => '12.10.2012'
), $selection->fetch()->toArray());



//SqlLiteral in where
$selection = $connection->table('author')->select('name')->where($whereLiteral);
$sql = $selection->getSql();
Assert::same('SELECT `name` FROM `author` WHERE (name LIKE "%david%")', $sql);
Assert::same(array(
	'name' => 'David Grudl'
), $selection->fetch()->toArray());



//SqlLiteral in group by and having (authors who are having 2 or more books)
$selection = $connection->table('author')->group($groupLiteral, $havingLiteral)->select('name, COUNT(book:id) books');
$sql = $selection->getSql();
Assert::same('SELECT `name`, COUNT(`book`.`id`) `books` FROM `author` LEFT JOIN `book` ON `author`.`id` = `book`.`author_id` GROUP BY author.id HAVING books >= 1', $sql);
Assert::same(array(
	'name' => 'Jakub Vrana',
	'books' => 2
), $selection->fetch()->toArray());



//SqlLiteral in order by (order authors by being translator, name)
$selection = $connection->table('author')->select('name')->order($orderLiteral)->order('name');
$sql = $selection->getSql();
Assert::same('SELECT `name` FROM `author` ORDER BY exists(select * from book where book.translator_id = author.id), `name`', $sql);
Assert::same(array(
	'name' => 'David Grudl'
), $selection->fetch()->toArray());

//SqlLiteral in order by with delimiting on (order authors by being translator)
$orderLiteral2->delimitable = true;
$selection = $connection->table('author')->select('name')->order($orderLiteral2);
$sql = $selection->getSql();
Assert::same('SELECT `name` FROM `author` ORDER BY EXISTS(SELECT * FROM `book` WHERE `book`.`translator_id` = `author`.`id`)', $sql);
Assert::same(array(
	'name' => 'Jakub Vrana'
), $selection->fetch()->toArray());

//SqlLiteral in order by with delimiting off and joining on (will create wrong join and thus throw exception)
$orderLiteral2->delimitable = false;
$orderLiteral2->examinable = true;
$selection = $connection->table('author')->select('name')->order($orderLiteral2);
$sql = $selection->getSql();

//take notice of wrong (reverse) join: "LEFT JOIN `book` ON `author`.`book_id` = `book`.`id`"
Assert::same('SELECT `name` FROM `author` LEFT JOIN `book` ON `author`.`book_id` = `book`.`id` ORDER BY EXISTS(SELECT * FROM book WHERE book.translator_id = author.id)', $sql);
try {
	$selection->fetch();
	Assert::fail('Should throw exception: ' . "Column not found: 1054 Unknown column 'author.book_id' in 'on clause'");
} catch (PDOException $exc) {
}
