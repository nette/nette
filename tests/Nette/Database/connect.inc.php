<?php

/**
 * Test: Nette\Database test boostrap.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 */

require __DIR__ . '/../bootstrap.php';


if (!is_file(__DIR__ . '/databases.ini')) {
	Tester\Environment::skip('Missing file databases.ini');
}

$options = Tester\DataProvider::load(__DIR__ . '/databases.ini', isset($query) ? $query : NULL);
$options = isset($_SERVER['argv'][1]) ? $options[$_SERVER['argv'][1]] : reset($options);
$options += array('user' => NULL, 'password' => NULL);

try {
	$connection = new Nette\Database\Connection($options['dsn'], $options['user'], $options['password']);
} catch (PDOException $e) {
	Tester\Environment::skip("Connection to '$options[dsn]' failed. Reason: " . $e->getMessage());
}

if (strpos($options['dsn'], 'sqlite::memory:') === FALSE) {
	Tester\Environment::lock($options['dsn'], dirname(TEMP_DIR));
}
$driverName = $connection->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);
$dao = new Nette\Database\SelectionFactory($connection);


/** Replaces [] with driver-specific quotes */
function reformat($s)
{
	global $driverName;
	if (is_array($s)) {
		if (isset($s[$driverName])) {
			return $s[$driverName];
		}
		$s = $s[0];
	}
	if ($driverName === 'mysql') {
		return strtr($s, '[]', '``');
	} elseif ($driverName === 'pgsql') {
		return strtr($s, '[]', '""');
	} elseif ($driverName === 'sqlsrv' || $driverName === 'sqlite' || $driverName === 'sqlite2') {
		return $s;
	} else {
		trigger_error("Unsupported driver $driverName", E_USER_WARNING);
	}
}
