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


$current = TestHelpers::getCurrentMultipleSection(__DIR__ . '/databases.ini');


try {
	$rc = new ReflectionClass('Nette\Database\Connection');
	/** @var Nette\Database\Connection */
	$connection = $rc->newInstanceArgs($current);

} catch (PDOException $e) {
	TestHelpers::skip("Connection to '$current[dsn]' failed. Reason: " . $e->getMessage());
}

TestHelpers::lock($current['dsn'], dirname(TEMP_DIR));

unset($current, $rc);
$driverName = $connection->getAttribute(PDO::ATTR_DRIVER_NAME);
