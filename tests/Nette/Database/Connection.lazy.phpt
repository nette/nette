<?php

/**
 * Test: Nette\Database\Connection lazy connection.
 *
 * @author     David Grudl
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/../bootstrap.php';



// non lazy
Assert::exception(function() {
	$connection = new Nette\Database\Connection('dsn', 'user', 'password');
}, 'PDOException', 'invalid data source name');


// lazy
$connection = new Nette\Database\Connection('dsn', 'user', 'password', array('lazy' => TRUE));
Assert::exception(function() use ($connection) {
	$connection->query('SELECT ?', 10);
}, 'PDOException', 'invalid data source name');


$connection = new Nette\Database\Connection('dsn', 'user', 'password', array('lazy' => TRUE));
Assert::exception(function() use ($connection) {
	$connection->quote('x');
}, 'PDOException', 'invalid data source name');
