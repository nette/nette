<?php

/**
 * Test: Nette\Database\SqlPreprocessor
 *
 * @author     David Grudl
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

use Nette\Database\SqlLiteral;


Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");


$preprocessor = new Nette\Database\SqlPreprocessor($connection);

// basic
list($sql, $params) = $preprocessor->process(array('SELECT id FROM author WHERE id = ?', 11));
Assert::same( 'SELECT id FROM author WHERE id = 11', $sql );
Assert::same( array(), $params );


list($sql, $params) = $preprocessor->process(array('SELECT id FROM author WHERE id =', 11));
Assert::same( 'SELECT id FROM author WHERE id = 11', $sql );
Assert::same( array(), $params );


list($sql, $params) = $preprocessor->process(array('SELECT id FROM author WHERE id = ? OR id = ?', 11, 12));
Assert::same( 'SELECT id FROM author WHERE id = 11 OR id = 12', $sql );
Assert::same( array(), $params );


list($sql, $params) = $preprocessor->process(array('SELECT id FROM author WHERE id = ?', 11, 'OR id = ?', 12));
Assert::same( 'SELECT id FROM author WHERE id = 11 OR id = 12', $sql );
Assert::same( array(), $params );


list($sql, $params) = $preprocessor->process(array('SELECT id FROM author WHERE id =', '?', 11, 'OR id = ?', 12));
Assert::same( 'SELECT id FROM author WHERE id = 11 OR id = 12', $sql );
Assert::same( array(), $params );


list($sql, $params) = $preprocessor->process(array('SELECT id FROM author WHERE id =', '? OR id = ?', 11, 12));
Assert::same( 'SELECT id FROM author WHERE id = 11 OR id = 12', $sql );
Assert::same( array(), $params );


// where
list($sql, $params) = $preprocessor->process(array('SELECT id FROM author WHERE', array(
	'id' => NULL,
	'name' => 'a',
	'born' => array(1, 2, 3),
	'web' => array(),
)));

Assert::same( reformat("SELECT id FROM author WHERE ([id] IS NULL) AND ([name] = 'a') AND ([born] IN (1, 2, 3)) AND (1=0)"), $sql );
Assert::same( array(), $params );


// order
list($sql, $params) = $preprocessor->process(array('SELECT id FROM author ORDER BY', array(
	'id' => TRUE,
	'name' => FALSE,
)));

Assert::same( reformat('SELECT id FROM author ORDER BY [id], [name] DESC'), $sql );
Assert::same( array(), $params );


// missing parameters
Assert::exception(function() use ($preprocessor) {
	$preprocessor->process(array('SELECT id FROM author WHERE id =', '? OR id = ?', 11));
}, 'Nette\InvalidArgumentException', 'There are more placeholders than passed parameters.');


// SqlLiteral
list($sql, $params) = $preprocessor->process(array('SELECT id FROM author WHERE id =', new SqlLiteral('? OR id = ?', 11, 12) ));
Assert::same( 'SELECT id FROM author WHERE id = ? OR id = ?', $sql );
Assert::same( array(11, 12), $params );


list($sql, $params) = $preprocessor->process(array('SELECT id FROM author WHERE', new SqlLiteral('id=11'), 'OR', new SqlLiteral('id=?', 12)));
Assert::same( 'SELECT id FROM author WHERE id=11 OR id=?', $sql );
Assert::same( array(12), $params );


list($sql, $params) = $preprocessor->process(array('SELECT id FROM author WHERE', array(
	'id' => new SqlLiteral('NULL'),
	'born' => array(1, 2, new SqlLiteral('3+1')),
	'web' => new SqlLiteral('NOW()'),
)));

Assert::same( reformat('SELECT id FROM author WHERE ([id] IS NULL) AND ([born] IN (1, 2, 3+1)) AND ([web] = NOW())'), $sql );
Assert::same( array(), $params );


// insert
list($sql, $params) = $preprocessor->process(array('INSERT INTO author',
	array('name' => 'Catelyn Stark', 'born' => new DateTime('2011-11-11')),
));

Assert::same( reformat("INSERT INTO author ([name], [born]) VALUES ('Catelyn Stark', '2011-11-11 00:00:00')"), $sql );
Assert::same( array(), $params );


// multi insert
list($sql, $params) = $preprocessor->process(array('INSERT INTO author', array(
	array('name' => 'Catelyn Stark', 'born' => new DateTime('2011-11-11')),
	array('name' => 'Sansa Stark', 'born' => new DateTime('2021-11-11'))
)));

Assert::same( reformat("INSERT INTO author ([name], [born]) VALUES ('Catelyn Stark', '2011-11-11 00:00:00'), ('Sansa Stark', '2021-11-11 00:00:00')"), $sql );
Assert::same( array(), $params );


// update
list($sql, $params) = $preprocessor->process(array('UPDATE author SET ?',
	array('id' => 12, 'name' => new SqlLiteral('UPPER(?)', 'John Doe')),
));

Assert::same( reformat("UPDATE author SET [id]=12, [name]=UPPER(?)"), $sql );
Assert::same( array('John Doe'), $params );


// multi & update
list($sql, $params) = $preprocessor->process(array('INSERT INTO author ? ON DUPLICATE KEY UPDATE ?',
	array('id' => 12, 'name' => 'John Doe'),
	array('web' => 'http://nette.org', 'name' => 'Dave Lister'),
));

Assert::same( reformat("INSERT INTO author ([id], [name]) VALUES (12, 'John Doe') ON DUPLICATE KEY UPDATE [web]='http://nette.org', [name]='Dave Lister'"), $sql );
Assert::same( array(), $params );
