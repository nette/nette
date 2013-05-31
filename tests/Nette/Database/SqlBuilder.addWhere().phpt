<?php

/**
 * Test: Nette\Database\Table\SqlBuilder: addWhere() and placeholders.
 *
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");

use Tester\Assert;
use Nette\Database\SqlLiteral;
use Nette\Database\Reflection\DiscoveredReflection;
use Nette\Database\Table\SqlBuilder;



$reflection = new DiscoveredReflection($connection);
$dao = new Nette\Database\SelectionFactory($connection, $reflection);
$sqlBuilder = array();

// test paramateres with NULL
$sqlBuilder[0] = new SqlBuilder('book', $connection, $reflection);
$sqlBuilder[0]->addWhere('id ? OR id ?', array(1, NULL));
$sqlBuilder[0]->addWhere('id ? OR id ?', array(1, NULL)); // duplicit condition

// test Selection as a parameter
$sqlBuilder[1] = new SqlBuilder('book', $connection, $reflection);
$sqlBuilder[1]->addWhere('id', $dao->table('book'));

// test Selection with column as a parameter
$sqlBuilder[2] = new SqlBuilder('book', $connection, $reflection);
$sqlBuilder[2]->addWhere('id', $dao->table('book')->select('id'));

// test multiple placeholder parameter
$sqlBuilder[3] = new SqlBuilder('book', $connection, $reflection);
$sqlBuilder[3]->addWhere('id ? OR id ?', NULL, $dao->table('book'));

// test SqlLiteral
$sqlBuilder[4] = new SqlBuilder('book', $connection, $reflection);
$sqlBuilder[4]->addWhere('id IN (?)', new SqlLiteral('1, 2, 3'));

// test auto type detection
$sqlBuilder[5] = new SqlBuilder('book', $connection, $reflection);
$sqlBuilder[5]->addWhere('id ? OR id ? OR id ?', 1, "test", array(1, 2));

// test empty array
$sqlBuilder[6] = new SqlBuilder('book', $connection, $reflection);
$sqlBuilder[6]->addWhere('id', array());
$sqlBuilder[6]->addWhere('id NOT', array());
$sqlBuilder[6]->addWhere('NOT (id ?)', array());

Assert::throws(function() use ($sqlBuilder) {
	$sqlBuilder[6]->addWhere('TRUE AND id', array());
}, 'Nette\InvalidArgumentException', 'Possible SQL query corruption. Add parentheses around operators.');
Assert::throws(function() use ($sqlBuilder) {
	$sqlBuilder[6]->addWhere('NOT id', array());
}, 'Nette\InvalidArgumentException', 'Possible SQL query corruption. Add parentheses around operators.');

// backward compatibility
$sqlBuilder[7] = new SqlBuilder('book', $connection, $reflection);
$sqlBuilder[7]->addWhere('id = ? OR id ? OR id IN ? OR id LIKE ? OR id > ?', 1, 2, array(1, 2), "%test", 3);
$sqlBuilder[7]->addWhere('name', "var");
$sqlBuilder[7]->addWhere('MAIN', 0); // "IN" is not considered as the operator

// auto operator tests
$sqlBuilder[8] = new SqlBuilder('book', $connection, $reflection);
$sqlBuilder[8]->addWhere('FOO(?)', 1);
$sqlBuilder[8]->addWhere('FOO(id, ?)', 1);
$sqlBuilder[8]->addWhere('id & ? = ?', 1, 1);
$sqlBuilder[8]->addWhere('?', 1);
$sqlBuilder[8]->addWhere('NOT ? OR ?', 1, 1);
$sqlBuilder[8]->addWhere('? + ? - ? / ? * ? % ?', 1, 1, 1, 1, 1, 1);

// tests multiline condition
$sqlBuilder[9] = new SqlBuilder('book', $connection, $reflection);
$sqlBuilder[9]->addWhere("\ncol1 ?\nOR col2 ?\n", 1, 1);

// tests NOT
$sqlBuilder[10] = new SqlBuilder('book', $connection, $reflection);
$sqlBuilder[10]->addWhere('id NOT', array(1, 2));
$sqlBuilder[10]->addWhere('id NOT', $dao->table('book')->select('id'));

// tests multi column IN clause
$sqlBuilder[11] = new SqlBuilder('book_tag', $connection, $reflection);
$sqlBuilder[11]->addWhere(array('book_id', 'tag_id'), array(array(1, 11), array(2, 12)));

Assert::same(reformat('SELECT * FROM [book] WHERE ([id] = ? OR [id] IS NULL)'), $sqlBuilder[0]->buildSelectQuery());
Assert::same(reformat('SELECT * FROM [book] WHERE ([id] IN (?))'), $sqlBuilder[4]->buildSelectQuery());
Assert::same(reformat('SELECT * FROM [book] WHERE ([id] = ? OR [id] = ? OR [id] IN (?))'), $sqlBuilder[5]->buildSelectQuery());
Assert::same(reformat('SELECT * FROM [book] WHERE ([id] IS NULL AND FALSE) AND ([id] IS NULL OR TRUE) AND (NOT ([id] IS NULL AND FALSE))'), $sqlBuilder[6]->buildSelectQuery());
Assert::same(reformat('SELECT * FROM [book] WHERE ([id] = ? OR [id] = ? OR [id] IN (?) OR [id] LIKE ? OR [id] > ?) AND ([name] = ?) AND (MAIN = ?)'), $sqlBuilder[7]->buildSelectQuery());
Assert::same(reformat('SELECT * FROM [book] WHERE (FOO(?)) AND (FOO([id], ?)) AND ([id] & ? = ?) AND (?) AND (NOT ? OR ?) AND (? + ? - ? / ? * ? % ?)'), $sqlBuilder[8]->buildSelectQuery());
Assert::same(reformat("SELECT * FROM [book] WHERE ([col1] = ?\nOR [col2] = ?)"), $sqlBuilder[9]->buildSelectQuery());

switch ($driverName) {
	case 'mysql':
		Assert::equal('SELECT * FROM `book` WHERE (`id` IN (?))', $sqlBuilder[1]->buildSelectQuery());
		Assert::equal('SELECT * FROM `book` WHERE (`id` IN (?))', $sqlBuilder[2]->buildSelectQuery());
		Assert::equal('SELECT * FROM `book` WHERE (`id` IS NULL OR `id` IN (?))', $sqlBuilder[3]->buildSelectQuery());
		Assert::equal('SELECT * FROM `book` WHERE (`id` NOT IN (?)) AND (`id` NOT IN (?))', $sqlBuilder[10]->buildSelectQuery());
		break;
	default:
		Assert::equal(reformat('SELECT * FROM [book] WHERE ([id] IN (SELECT [id] FROM [book]))'), $sqlBuilder[1]->buildSelectQuery());
		Assert::equal(reformat('SELECT * FROM [book] WHERE ([id] IN (SELECT [id] FROM [book]))'), $sqlBuilder[2]->buildSelectQuery());
		Assert::equal(reformat('SELECT * FROM [book] WHERE ([id] IS NULL OR [id] IN (SELECT [id] FROM [book]))'), $sqlBuilder[3]->buildSelectQuery());
		Assert::equal(reformat('SELECT * FROM [book] WHERE ([id] NOT IN (?)) AND ([id] NOT IN (SELECT [id] FROM [book]))'), $sqlBuilder[10]->buildSelectQuery());
}

switch ($driverName) {
	case 'sqlite':
		Assert::equal('SELECT * FROM [book_tag] WHERE (([book_id] = ? AND [tag_id] = ?) OR ([book_id] = ? AND [tag_id] = ?))', $sqlBuilder[11]->buildSelectQuery());
		break;
	default:
		Assert::equal(reformat('SELECT * FROM [book_tag] WHERE (([book_id], [tag_id]) IN (?))'), $sqlBuilder[11]->buildSelectQuery());
}



$books = $dao->table('book')->where('id',
	$dao->table('book_tag')->select('book_id')->where('tag_id', 21)
);
Assert::same(3, $books->count());

Assert::exception(function() use ($dao) {
	$dao->table('book')->where('id',
		$dao->table('book_tag')->where('tag_id', 21)
	);
}, 'Nette\InvalidArgumentException', 'Selection argument must have defined a select column.');

switch ($driverName) {
	case 'mysql':
		$connection->query('CREATE INDEX book_tag_unique ON book_tag (book_id, tag_id)');
		$connection->query('ALTER TABLE book_tag DROP PRIMARY KEY');
		break;
	case 'pgsql':
		$connection->query('ALTER TABLE book_tag DROP CONSTRAINT "book_tag_pkey"');
		break;
	case 'sqlite':
		// dropping constraint or column is not supported
		$connection->query('
			CREATE TABLE book_tag_temp (
				book_id INTEGER NOT NULL,
				tag_id INTEGER NOT NULL,
				CONSTRAINT book_tag_tag FOREIGN KEY (tag_id) REFERENCES tag (id),
				CONSTRAINT book_tag_book FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE
			)
		');
		$connection->query('INSERT INTO book_tag_temp SELECT book_id, tag_id FROM book_tag');
		$connection->query('DROP TABLE book_tag');
		$connection->query('ALTER TABLE book_tag_temp RENAME TO book_tag');
		break;
	case 'sqlsrv':
		$connection->query('ALTER TABLE book_tag DROP CONSTRAINT PK_book_tag');
		break;
	default:
		Assert::fail("Unsupported driver $driverName");
}

$reflection = new DiscoveredReflection($connection);
$dao = new Nette\Database\SelectionFactory($connection, $reflection);

$e = Assert::exception(function() use ($dao) {
	$books = $dao->table('book')->where('id',
		$dao->table('book_tag')->where('tag_id', 21)
	);
	$books->fetch();
}, 'Nette\InvalidArgumentException', 'Selection argument must have defined a select column.');

Assert::exception(function() use ($e) {
	throw $e->getPrevious();
}, 'LogicException', 'Table "book_tag" does not have a primary key.');


Assert::exception(function() use ($connection, $reflection) {
	$sqlBuilder = new SqlBuilder('book', $connection, $reflection);
	$sqlBuilder->addWhere('id ?');
}, 'Nette\InvalidArgumentException', 'Argument count does not match placeholder count.');



Assert::exception(function() use ($connection, $reflection) {
	$sqlBuilder = new SqlBuilder('book', $connection, $reflection);
	$sqlBuilder->addWhere('id = ?', NULL);
}, 'Nette\InvalidArgumentException', 'Column operator does not accept NULL argument.');



Assert::exception(function() use ($connection, $reflection) {
	$sqlBuilder = new SqlBuilder('book', $connection, $reflection);
	$sqlBuilder->addWhere('id = ?', array(1, 2));
}, 'Nette\InvalidArgumentException', 'Column operator does not accept array argument.');
