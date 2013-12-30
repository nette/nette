<?php

/**
 * Test: Nette\Database\Table\SqlBuilder: addWhere() and placeholders.
 *
 * @author     Jan Skrasek
 * @dataProvider? databases.ini
 */

use Tester\Assert;
use Nette\Database\SqlLiteral;
use Nette\Database\Reflection\DiscoveredReflection;
use Nette\Database\Table\SqlBuilder;

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");



$reflection = new DiscoveredReflection($connection);
$dao = new Nette\Database\Context($connection, $reflection);


test(function() use ($connection, $reflection) { // test paramateres with NULL
	$sqlBuilder = new SqlBuilder('book', $connection, $reflection);
	$sqlBuilder->addWhere('id ? OR id ?', array(1, NULL));
	$sqlBuilder->addWhere('id ? OR id ?', array(1, NULL)); // duplicit condition
	Assert::same(reformat('SELECT * FROM [book] WHERE ([id] = ? OR [id] IS NULL)'), $sqlBuilder->buildSelectQuery());
});


test(function() use ($dao, $connection, $reflection) { // test Selection as a parameter
	$sqlBuilder = new SqlBuilder('book', $connection, $reflection);
	$sqlBuilder->addWhere('id', $dao->table('book'));
	Assert::equal(reformat(array(
		'mysql' => 'SELECT * FROM `book` WHERE (`id` IN (?))',
		'SELECT * FROM [book] WHERE ([id] IN (SELECT [id] FROM [book]))',
	)), $sqlBuilder->buildSelectQuery());
});


test(function() use ($dao, $connection, $reflection) { // test Selection with column as a parameter
	$sqlBuilder = new SqlBuilder('book', $connection, $reflection);
	$sqlBuilder->addWhere('id', $dao->table('book')->select('id'));
	Assert::equal(reformat(array(
		'mysql' => 'SELECT * FROM `book` WHERE (`id` IN (?))',
		'SELECT * FROM [book] WHERE ([id] IN (SELECT [id] FROM [book]))',
	)), $sqlBuilder->buildSelectQuery());
});


test(function() use ($dao, $connection, $reflection) { // test multiple placeholder parameter
	$sqlBuilder = new SqlBuilder('book', $connection, $reflection);
	$sqlBuilder->addWhere('id ? OR id ?', NULL, $dao->table('book'));
	Assert::equal(reformat(array(
		'mysql' => 'SELECT * FROM `book` WHERE (`id` IS NULL OR `id` IN (?))',
		'SELECT * FROM [book] WHERE ([id] IS NULL OR [id] IN (SELECT [id] FROM [book]))',
	)), $sqlBuilder->buildSelectQuery());
});


test(function() use ($connection, $reflection) { // test SqlLiteral
	$sqlBuilder = new SqlBuilder('book', $connection, $reflection);
	$sqlBuilder->addWhere('id IN (?)', new SqlLiteral('1, 2, 3'));
	Assert::same(reformat('SELECT * FROM [book] WHERE ([id] IN (?))'), $sqlBuilder->buildSelectQuery());
});


test(function() use ($connection, $reflection) { // test auto type detection
	$sqlBuilder = new SqlBuilder('book', $connection, $reflection);
	$sqlBuilder->addWhere('id ? OR id ? OR id ?', 1, "test", array(1, 2));
	Assert::same(reformat('SELECT * FROM [book] WHERE ([id] = ? OR [id] = ? OR [id] IN (?))'), $sqlBuilder->buildSelectQuery());
});


test(function() use ($connection, $reflection) { // test empty array
	$sqlBuilder = new SqlBuilder('book', $connection, $reflection);
	$sqlBuilder->addWhere('id', array());
	$sqlBuilder->addWhere('id NOT', array());
	$sqlBuilder->addWhere('NOT (id ?)', array());

	Assert::exception(function() use ($sqlBuilder) {
		$sqlBuilder->addWhere('TRUE AND id', array());
	}, 'Nette\InvalidArgumentException', 'Possible SQL query corruption. Add parentheses around operators.');

	Assert::exception(function() use ($sqlBuilder) {
		$sqlBuilder->addWhere('NOT id', array());
	}, 'Nette\InvalidArgumentException', 'Possible SQL query corruption. Add parentheses around operators.');

	Assert::same(reformat('SELECT * FROM [book] WHERE ([id] IS NULL AND FALSE) AND ([id] IS NULL OR TRUE) AND (NOT ([id] IS NULL AND FALSE))'), $sqlBuilder->buildSelectQuery());
});


test(function() use ($connection, $reflection) { // backward compatibility
	$sqlBuilder = new SqlBuilder('book', $connection, $reflection);
	$sqlBuilder->addWhere('id = ? OR id ? OR id IN ? OR id LIKE ? OR id > ?', 1, 2, array(1, 2), "%test", 3);
	$sqlBuilder->addWhere('name', "var");
	$sqlBuilder->addWhere('MAIN', 0); // "IN" is not considered as the operator
	$sqlBuilder->addWhere('id IN (?)', array(1, 2));
	Assert::same(reformat('SELECT * FROM [book] WHERE ([id] = ? OR [id] = ? OR [id] IN (?) OR [id] LIKE ? OR [id] > ?) AND ([name] = ?) AND (MAIN = ?) AND ([id] IN (?))'), $sqlBuilder->buildSelectQuery());
});


test(function() use ($connection, $reflection) { // auto operator tests
	$sqlBuilder = new SqlBuilder('book', $connection, $reflection);
	$sqlBuilder->addWhere('FOO(?)', 1);
	$sqlBuilder->addWhere('FOO(id, ?)', 1);
	$sqlBuilder->addWhere('id & ? = ?', 1, 1);
	$sqlBuilder->addWhere('?', 1);
	$sqlBuilder->addWhere('NOT ? OR ?', 1, 1);
	$sqlBuilder->addWhere('? + ? - ? / ? * ? % ?', 1, 1, 1, 1, 1, 1);
	Assert::same(reformat('SELECT * FROM [book] WHERE (FOO(?)) AND (FOO([id], ?)) AND ([id] & ? = ?) AND (?) AND (NOT ? OR ?) AND (? + ? - ? / ? * ? % ?)'), $sqlBuilder->buildSelectQuery());
});


test(function() use ($connection, $reflection) { // tests multiline condition
	$sqlBuilder = new SqlBuilder('book', $connection, $reflection);
	$sqlBuilder->addWhere("\ncol1 ?\nOR col2 ?\n", 1, 1);
	Assert::same(reformat("SELECT * FROM [book] WHERE ([col1] = ?\nOR [col2] = ?)"), $sqlBuilder->buildSelectQuery());
});


test(function() use ($dao, $connection, $reflection) { // tests NOT
	$sqlBuilder = new SqlBuilder('book', $connection, $reflection);
	$sqlBuilder->addWhere('id NOT', array(1, 2));
	$sqlBuilder->addWhere('id NOT', $dao->table('book')->select('id'));
	Assert::equal(reformat(array(
		'mysql' => 'SELECT * FROM `book` WHERE (`id` NOT IN (?)) AND (`id` NOT IN (?))',
		'SELECT * FROM [book] WHERE ([id] NOT IN (?)) AND ([id] NOT IN (SELECT [id] FROM [book]))',
	)), $sqlBuilder->buildSelectQuery());
});


test(function() use ($connection, $reflection) { // tests multi column IN clause
	$sqlBuilder = new SqlBuilder('book_tag', $connection, $reflection);
	$sqlBuilder->addWhere(array('book_id', 'tag_id'), array(array(1, 11), array(2, 12)));
	Assert::equal(reformat(array(
		'sqlite' => 'SELECT * FROM [book_tag] WHERE (([book_id] = ? AND [tag_id] = ?) OR ([book_id] = ? AND [tag_id] = ?))',
		'mysql' => 'SELECT * FROM `book_tag` WHERE ((`book_id` = ? AND `tag_id` = ?) OR (`book_id` = ? AND `tag_id` = ?))',
		'SELECT * FROM [book_tag] WHERE (([book_id], [tag_id]) IN (?))',
	)), $sqlBuilder->buildSelectQuery());
});


test(function() use ($connection, $reflection) { // tests operator suffix
	$sqlBuilder = new SqlBuilder('book', $connection, $reflection);
	$sqlBuilder->addWhere('id <> ? OR id >= ?', 1, 2);
	Assert::same(reformat("SELECT * FROM [book] WHERE ([id] <> ? OR [id] >= ?)"), $sqlBuilder->buildSelectQuery());
});


test(function() use ($dao) {
	$books = $dao->table('book')->where('id',
		$dao->table('book_tag')->select('book_id')->where('tag_id', 21)
	);
	Assert::same(3, $books->count());
});


Assert::exception(function() use ($dao) {
	$dao->table('book')->where('id',
		$dao->table('book_tag')->where('tag_id', 21)
	);
}, 'Nette\InvalidArgumentException', 'Selection argument must have defined a select column.');


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


test(function() use ($driverName, $context, $connection, $reflection) {
	switch ($driverName) {
		case 'mysql':
			$context->query('CREATE INDEX book_tag_unique ON book_tag (book_id, tag_id)');
			$context->query('ALTER TABLE book_tag DROP PRIMARY KEY');
			break;
		case 'pgsql':
			$context->query('ALTER TABLE book_tag DROP CONSTRAINT "book_tag_pkey"');
			break;
		case 'sqlite':
			// dropping constraint or column is not supported
			$context->query('
				CREATE TABLE book_tag_temp (
					book_id INTEGER NOT NULL,
					tag_id INTEGER NOT NULL,
					CONSTRAINT book_tag_tag FOREIGN KEY (tag_id) REFERENCES tag (id),
					CONSTRAINT book_tag_book FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE
				)
			');
			$context->query('INSERT INTO book_tag_temp SELECT book_id, tag_id FROM book_tag');
			$context->query('DROP TABLE book_tag');
			$context->query('ALTER TABLE book_tag_temp RENAME TO book_tag');
			break;
		case 'sqlsrv':
			$context->query('ALTER TABLE book_tag DROP CONSTRAINT PK_book_tag');
			break;
		default:
			Assert::fail("Unsupported driver $driverName");
	}

	$reflection = new DiscoveredReflection($connection);
	$dao = new Nette\Database\Context($connection, $reflection);

	$e = Assert::exception(function() use ($dao) {
		$books = $dao->table('book')->where('id',
			$dao->table('book_tag')->where('tag_id', 21)
		);
		$books->fetch();
	}, 'Nette\InvalidArgumentException', 'Selection argument must have defined a select column.');

	Assert::exception(function() use ($e) {
		throw $e->getPrevious();
	}, 'LogicException', 'Table "book_tag" does not have a primary key.');
});
