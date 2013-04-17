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


Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");


$preprocessor = new Nette\Database\SqlPreprocessor($connection);

// basic
list($sql, $params) = $preprocessor->process('SELECT id FROM author WHERE id = ?', array(11));
Assert::same( 'SELECT id FROM author WHERE id = 11', $sql );
Assert::same( array(), $params );


list($sql, $params) = $preprocessor->process('SELECT id FROM author WHERE id =', array(11));
Assert::same( 'SELECT id FROM author WHERE id = 11', $sql );
Assert::same( array(), $params );


list($sql, $params) = $preprocessor->process('SELECT id FROM author WHERE id = ? OR id = ?', array(11, 12));
Assert::same( 'SELECT id FROM author WHERE id = 11 OR id = 12', $sql );
Assert::same( array(), $params );


// missing parameters
Assert::exception(function() use ($preprocessor) {
	$preprocessor->process('SELECT id FROM author WHERE id = ? OR id = ?', array(12));
}, 'Nette\InvalidArgumentException', 'There are more placeholders than passed parameters.');


// SqlLiteral
list($sql, $params) = $preprocessor->process('SELECT id FROM author WHERE id =', array(new SqlLiteral('NOW()') ));
Assert::same( 'SELECT id FROM author WHERE id = NOW()', $sql );
Assert::same( array(), $params );


// insert
list($sql, $params) = $preprocessor->process('INSERT INTO author', array(array(
	array('name' => 'Catelyn Stark', 'born' => new DateTime('2011-11-11')),
)));

Assert::same( reformat("INSERT INTO author ([name], [born]) VALUES ('Catelyn Stark', '2011-11-11 00:00:00')"), $sql );
Assert::same( array(), $params );


// multi insert
list($sql, $params) = $preprocessor->process('INSERT INTO author', array(array(
	array('name' => 'Catelyn Stark', 'born' => new DateTime('2011-11-11')),
	array('name' => 'Sansa Stark', 'born' => new DateTime('2021-11-11'))
)));

Assert::same( reformat("INSERT INTO author ([name], [born]) VALUES ('Catelyn Stark', '2011-11-11 00:00:00'), ('Sansa Stark', '2021-11-11 00:00:00')"), $sql );
Assert::same( array(), $params );


// update
list($sql, $params) = $preprocessor->process('UPDATE author SET ?', array(
	array('id' => 12, 'name' => 'John Doe'),
));

Assert::same( reformat("UPDATE author SET [id]=12, [name]='John Doe'"), $sql );
Assert::same( array(), $params );


// multi & update
list($sql, $params) = $preprocessor->process('INSERT INTO author ? ON DUPLICATE KEY UPDATE ?', array(
	array('id' => 12, 'name' => 'John Doe'),
	array('web' => 'http://nette.org', 'name' => 'Dave Lister'),
));

Assert::same( reformat("INSERT INTO author ([id], [name]) VALUES (12, 'John Doe') ON DUPLICATE KEY UPDATE [web]='http://nette.org', [name]='Dave Lister'"), $sql );
Assert::same( array(), $params );
