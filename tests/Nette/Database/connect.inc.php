<?php

/**
 * Test: Nette\Database test boostap.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

use Nette\Database;



$driverName = NULL;
if (isset($_ENV['NETTE_DATABASE_DRIVER'])) { // CLI
	$driverName = $_ENV['NETTE_DATABASE_DRIVER'];

} elseif (isset($_SERVER['NETTE_DATABASE_DRIVER'])) { // CGI
	$driverName = $_SERVER['NETTE_DATABASE_DRIVER'];
}



require __DIR__ . '/../bootstrap.php';



try {
	switch ($driverName) {
		case NULL:
			TestHelpers::skip('Missing NETTE_DATABASE_DRIVER environment variable.');
			break;

		case 'mysql':
			$connection = new Database\Connection('mysql:host=localhost', 'root');
			break;

		default:
			TestHelpers::skip("Database driver '$driverName' is not supported.");
			break;
	}

} catch (PDOException $e) {
	TestHelpers::skip("Requires correctly configured $driverName connection database. Connection failed reason: " . $e->getMessage());
}

flock($lock = fopen(TEMP_DIR . "/../lock-db-$driverName", 'w'), LOCK_EX);
