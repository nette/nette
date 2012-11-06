<?php

/**
 * Test: Nette\Database test boostap.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 */

require __DIR__ . '/../bootstrap.php';


$config = parse_ini_file(__DIR__ . '/databases.ini', TRUE);
$current = isset($_SERVER['argv'][1]) ? $config[$_SERVER['argv'][1]] : reset($config);


try {
	$rc = new ReflectionClass('Nette\Database\Connection');
	/** @var Nette\Database\Connection */
	$connection = $rc->newInstanceArgs($current);

} catch (PDOException $e) {
	Tester\Helpers::skip("Connection to '$current[dsn]' failed. Reason: " . $e->getMessage());
}

Tester\Helpers::lock($current['dsn'], dirname(TEMP_DIR));

unset($config, $current, $rc);
$driverName = $connection->getAttribute(PDO::ATTR_DRIVER_NAME);
