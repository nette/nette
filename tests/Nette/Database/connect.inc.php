<?php

/**
 * Test: Nette\Database test boostap.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require __DIR__ . '/../bootstrap.php';


$config = parse_ini_file(__DIR__ . '/databases.ini', TRUE);
$current = isset($_SERVER['argv'][1]) ? $config[$_SERVER['argv'][1]] : reset($config);


try {
	$rc = new ReflectionClass('Nette\Database\Connection');
	/** @var Nette\Database\Connection */
	$connection = $rc->newInstanceArgs($current);

} catch (PDOException $e) {
	TestHelpers::skip("Connection to '$current[dsn]' failed. Reason: " . $e->getMessage());
}

flock($lock = fopen(TEMP_DIR . '/../lock-db-' . md5($current['dsn']), 'w'), LOCK_EX);

unset($config, $current, $rc);
$driverName = $connection->getAttribute(PDO::ATTR_DRIVER_NAME);
