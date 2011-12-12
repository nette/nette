<?php

/**
 * Test: Nette\Database test boostap.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Database;



require __DIR__ . '/../bootstrap.php';



$dbStructure = __DIR__ . '/nette_test.sql';

try {
	$connection = new Database\Connection("mysql:host=localhost", "root");
	$connection->loadFile($dbStructure);

} catch (\PDOException $e) {
	TestHelpers::skip('Requires corretly configured mysql connection and "nette_test" database.');

}
