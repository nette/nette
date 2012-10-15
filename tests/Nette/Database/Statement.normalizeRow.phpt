<?php

/**
 * Test: Nette\Database\Statement::normalizeRow()
 *
 * @author     David Grudl
 * @package    Nette\Database
 * @multiple   databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection



$assertsFile = __DIR__ . "/Statement.normalizeRow.$driverName.php";
if (!file_exists($assertsFile)) {
	TestHelpers::skip("Missing asserts file '$assertsFile'.");
}



Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test3.sql");

require $assertsFile;
