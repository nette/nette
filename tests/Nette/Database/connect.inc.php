<?php

/**
 * Test: Nette\Database test boostap.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 */

require __DIR__ . '/../bootstrap.php';


if (!is_file(__DIR__ . '/databases.ini')) {
	Tester\Helpers::skip();
}

$options = Tester\DataProvider::load(__DIR__ . '/databases.ini', isset($query) ? $query : NULL);
$options = isset($_SERVER['argv'][1]) ? $options[$_SERVER['argv'][1]] : reset($options);
$options += array('user' => NULL, 'password' => NULL);

try {
	$connection = new Nette\Database\Connection($options['dsn'], $options['user'], $options['password']);
} catch (PDOException $e) {
	Tester\Helpers::skip("Connection to '$options[dsn]' failed. Reason: " . $e->getMessage());
}

if (stripos($options['dsn'], 'sqlite::memory:') !== 0) {
	Tester\Helpers::lock($options['dsn'], dirname(TEMP_DIR));
}
$driverName = $connection->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);
