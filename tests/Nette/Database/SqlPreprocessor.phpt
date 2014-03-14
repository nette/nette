<?php

/**
 * Test: Nette\Database\SqlPreprocessor
 *
 * @author     David Grudl
 * @dataProvider? databases.ini
 */

use Tester\Assert;
use Nette\Database\SqlLiteral;

require __DIR__ . '/connect.inc.php'; // create $connection


$preprocessor = new Nette\Database\SqlPreprocessor($connection);

test(function() use ($preprocessor) { // basic
	list($sql, $params) = $preprocessor->process(array('SELECT id FROM author WHERE id = ?', 11));
	Assert::same( 'SELECT id FROM author WHERE id = 11', $sql );
	Assert::same( array(), $params );
});


test(function() use ($preprocessor) {
	list($sql, $params) = $preprocessor->process(array('SELECT id FROM author WHERE id =', 11));
	Assert::same( 'SELECT id FROM author WHERE id = 11', $sql );
	Assert::same( array(), $params );
});


test(function() use ($preprocessor) {
	list($sql, $params) = $preprocessor->process(array('SELECT id FROM author WHERE id = ? OR id = ?', 11, 12));
	Assert::same( 'SELECT id FROM author WHERE id = 11 OR id = 12', $sql );
	Assert::same( array(), $params );
});


test(function() use ($preprocessor) {
	list($sql, $params) = $preprocessor->process(array('SELECT id FROM author WHERE id = ?', 11, 'OR id = ?', 12));
	Assert::same( 'SELECT id FROM author WHERE id = 11 OR id = 12', $sql );
	Assert::same( array(), $params );
});


test(function() use ($preprocessor) {
	list($sql, $params) = $preprocessor->process(array('SELECT id FROM author WHERE id =', '?', 11, 'OR id = ?', 12));
	Assert::same( 'SELECT id FROM author WHERE id = 11 OR id = 12', $sql );
	Assert::same( array(), $params );
});


test(function() use ($preprocessor) {
	list($sql, $params) = $preprocessor->process(array('SELECT id FROM author WHERE id =', '? OR id = ?', 11, 12));
	Assert::same( 'SELECT id FROM author WHERE id = 11 OR id = 12', $sql );
	Assert::same( array(), $params );
});


test(function() use ($preprocessor) { // comments
	list($sql, $params) = $preprocessor->process(array("SELECT id --?\nFROM author WHERE id = ?", 11));
	Assert::same( "SELECT id --?\nFROM author WHERE id = 11", $sql );
	Assert::same( array(), $params );

	list($sql, $params) = $preprocessor->process(array("SELECT id /* ? \n */FROM author WHERE id = ? --*/", 11));
	Assert::same( "SELECT id /* ? \n */FROM author WHERE id = 11 --*/", $sql );
	Assert::same( array(), $params );
});


test(function() use ($preprocessor) { // strings
	list($sql, $params) = $preprocessor->process(array("SELECT id, '?' FROM author WHERE id = ?", 11));
	Assert::same( "SELECT id, '?' FROM author WHERE id = 11", $sql );
	Assert::same( array(), $params );

	list($sql, $params) = $preprocessor->process(array('SELECT id, "?" FROM author WHERE id = ?', 11));
	Assert::same( 'SELECT id, "?" FROM author WHERE id = 11', $sql );
	Assert::same( array(), $params );
});


test(function() use ($preprocessor) { // where
	list($sql, $params) = $preprocessor->process(array('SELECT id FROM author WHERE', array(
		'id' => NULL,
		'name' => 'a',
		'born' => array(1, 2, 3),
		'web' => array(),
	)));

	Assert::same( reformat("SELECT id FROM author WHERE ([id] IS NULL) AND ([name] = 'a') AND ([born] IN (1, 2, 3)) AND (1=0)"), $sql );
	Assert::same( array(), $params );
});


test(function() use ($preprocessor) {
	list($sql, $params) = $preprocessor->process(array('SELECT * FROM book_tag WHERE (book_id, tag_id) IN (?)', array(
		array(1, 2),
		array(3, 4),
		array(5, 6),
	)));

	Assert::same( reformat("SELECT * FROM book_tag WHERE (book_id, tag_id) IN ((1, 2), (3, 4), (5, 6))"), $sql );
	Assert::same( array(), $params );
});


test(function() use ($preprocessor) { // order
	list($sql, $params) = $preprocessor->process(array('SELECT id FROM author ORDER BY', array(
		'id' => TRUE,
		'name' => FALSE,
	)));

	Assert::same( reformat('SELECT id FROM author ORDER BY [id], [name] DESC'), $sql );
	Assert::same( array(), $params );
});


test(function() use ($preprocessor) { // missing parameters
	Assert::exception(function() use ($preprocessor) {
		$preprocessor->process(array('SELECT id FROM author WHERE id =', '? OR id = ?', 11));
	}, 'Nette\InvalidArgumentException', 'There are more placeholders than passed parameters.');
});


test(function() use ($preprocessor) { // SqlLiteral
	list($sql, $params) = $preprocessor->process(array('SELECT id FROM author WHERE id =', new SqlLiteral('? OR id = ?', array(11, 12)) ));
	Assert::same( 'SELECT id FROM author WHERE id = ? OR id = ?', $sql );
	Assert::same( array(11, 12), $params );
});


test(function() use ($preprocessor) {
	list($sql, $params) = $preprocessor->process(array('SELECT id FROM author WHERE', new SqlLiteral('id=11'), 'OR', new SqlLiteral('id=?', array(12))));
	Assert::same( 'SELECT id FROM author WHERE id=11 OR id=?', $sql );
	Assert::same( array(12), $params );
});


test(function() use ($preprocessor) {
	list($sql, $params) = $preprocessor->process(array('SELECT id FROM author WHERE', array(
		'id' => new SqlLiteral('NULL'),
		'born' => array(1, 2, new SqlLiteral('3+1')),
		'web' => new SqlLiteral('NOW()'),
	)));

	Assert::same( reformat('SELECT id FROM author WHERE ([id] IS NULL) AND ([born] IN (1, 2, 3+1)) AND ([web] = NOW())'), $sql );
	Assert::same( array(), $params );
});


test(function() use ($preprocessor, $driverName) { // insert
	list($sql, $params) = $preprocessor->process(array('INSERT INTO author',
		array('name' => 'Catelyn Stark', 'born' => new DateTime('2011-11-11')),
	));

	Assert::same( reformat(array(
		'sqlite' => "INSERT INTO author ([name], [born]) SELECT 'Catelyn Stark', 1320966000",
		"INSERT INTO author ([name], [born]) VALUES ('Catelyn Stark', '2011-11-11 00:00:00')",
	)), $sql );
	Assert::same( array(), $params );
});


test(function() use ($preprocessor, $driverName) { // multi insert
	list($sql, $params) = $preprocessor->process(array('INSERT INTO author', array(
		array('name' => 'Catelyn Stark', 'born' => new DateTime('2011-11-11')),
		array('name' => 'Sansa Stark', 'born' => new DateTime('2021-11-11'))
	)));

	Assert::same( reformat(array(
		'sqlite' => "INSERT INTO author ([name], [born]) SELECT 'Catelyn Stark', 1320966000 UNION ALL SELECT 'Sansa Stark', 1636585200",
		"INSERT INTO author ([name], [born]) VALUES ('Catelyn Stark', '2011-11-11 00:00:00'), ('Sansa Stark', '2021-11-11 00:00:00')",
	)), $sql );
	Assert::same( array(), $params );
});


test(function() use ($preprocessor) { // update
	list($sql, $params) = $preprocessor->process(array('UPDATE author SET ?',
		array('id' => 12, 'name' => new SqlLiteral('UPPER(?)', array('John Doe'))),
	));

	Assert::same( reformat("UPDATE author SET [id]=12, [name]=UPPER(?)"), $sql );
	Assert::same( array('John Doe'), $params );
});


test(function() use ($preprocessor) { // update +=
	list($sql, $params) = $preprocessor->process(array('UPDATE author SET ?',
		array('id+=' => 1, 'id-=' => -1),
	));

	Assert::same( reformat("UPDATE author SET [id]=[id] + 1, [id]=[id] - -1"), $sql );
});


test(function() use ($preprocessor, $driverName) { // multi & update
	list($sql, $params) = $preprocessor->process(array('INSERT INTO author ? ON DUPLICATE KEY UPDATE ?',
		array('id' => 12, 'name' => 'John Doe'),
		array('web' => 'http://nette.org', 'name' => 'Dave Lister'),
	));

	Assert::same( reformat(array(
	'sqlite' => "INSERT INTO author ([id], [name]) SELECT 12, 'John Doe' ON DUPLICATE KEY UPDATE [web]='http://nette.org', [name]='Dave Lister'",
	"INSERT INTO author ([id], [name]) VALUES (12, 'John Doe') ON DUPLICATE KEY UPDATE [web]='http://nette.org', [name]='Dave Lister'",
	)), $sql );
	Assert::same( array(), $params );
});
