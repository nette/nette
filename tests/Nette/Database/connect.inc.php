<?php

/**
 * Test: Nette\Database test boostap.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

$connectionArgs = NULL;
if (isset($_ENV['TESTRUNNER_DB_ARGS'])) {
	$connectionArgs = $_ENV['TESTRUNNER_DB_ARGS'];

} elseif (isset($_SERVER['TESTRUNNER_DB_ARGS'])) {
	$connectionArgs = $_SERVER['TESTRUNNER_DB_ARGS'];
}


require __DIR__ . '/../bootstrap.php';


if ($connectionArgs === NULL) {
	TestHelpers::skip('Missing TESTRUNNER_DB_ARGS environment variable.');
}

$connectionArgs = @unserialize(base64_decode($connectionArgs));
if (!is_array($connectionArgs)) {
	TestHelpers::skip('Malformed TESTRUNNER_DB_ARGS environment variable content.');
}



try {
	$rc = new ReflectionClass('Nette\Database\Connection');
	$connection = $rc->newInstanceArgs($connectionArgs);

} catch (PDOException $e) {
	TestHelpers::skip("Connection to '$connectionArgs[dsn]' failed. Reason: " . $e->getMessage());
}

flock($lock = fopen(TEMP_DIR . '/../lock-db-' . md5($connectionArgs['dsn']), 'w'), LOCK_EX);

unset($rc, $connectionArgs);
