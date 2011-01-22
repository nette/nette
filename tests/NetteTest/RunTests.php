
Nette Test Framework (v0.4)
---------------------------
<?php

require_once __DIR__ . '/TestRunner.php';

/**
 * Help
 */
if (!isset($_SERVER['argv'][1])) { ?>
Usage:
	php RunTests.php [options] [file or directory]

Options:
	-p <php>    Specify PHP-CGI executable to run.
	-c <path>   Look for php.ini in directory <path> or use <path> as php.ini.
	-d key=val  Define INI entry 'key' with value 'val'.
	-l <path>   Specify path to shared library files (LD_LIBRARY_PATH)
	-s          Show information about skipped tests

<?php
}



/**
 * Execute tests
 */
try {
	if (file_exists(__DIR__ . '/coverage.dat')) {
		unlink(__DIR__ . '/coverage.dat');
	}


	$manager = new TestRunner;
	$manager->parseArguments();
	$res = $manager->run();
	die($res ? 0 : 1);

} catch (Exception $e) {
	echo 'Error: ', $e->getMessage(), "\n";
	die(2);
}
