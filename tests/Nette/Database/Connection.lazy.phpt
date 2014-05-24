<?php

/**
 * Test: Nette\Database\Connection lazy connection.
 * @dataProvider? databases.ini
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


if (!class_exists('PDO')) {
	Tester\Environment::skip('Requires PHP extension PDO.');
}


test(function() { // non lazy
	Assert::exception(function() {
		$connection = new Nette\Database\Connection('dsn', 'user', 'password');
	}, 'PDOException', 'invalid data source name');
});


test(function() { // lazy
	$connection = new Nette\Database\Connection('dsn', 'user', 'password', array('lazy' => TRUE));
	$context = new Nette\Database\Context($connection);
	Assert::exception(function() use ($context) {
		$context->query('SELECT ?', 10);
	}, 'PDOException', 'invalid data source name');
});


test(function() {
	$connection = new Nette\Database\Connection('dsn', 'user', 'password', array('lazy' => TRUE));
	Assert::exception(function() use ($connection) {
		$connection->quote('x');
	}, 'PDOException', 'invalid data source name');
});
