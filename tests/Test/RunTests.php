
Nette Test Framework (v0.4)
---------------------------
<?php

require_once __DIR__ . '/TestRunner.php';

/**
 * Help
 */
if (($showHelp = in_array('-h', $_SERVER['argv']) || in_array('--help', $_SERVER['argv'])) || $_SERVER['argc'] === 1) { ?>
Usage:
  php RunTests.php [options] [file or directory]

Options:
  -p <php>       Specify PHP-CGI executable to run.
  -c <path>      Look for php.ini in directory <path> or use <path> as php.ini.
  -log <path>    Write log to file <path>.
  -d key=val     Define INI entry 'key' with value 'val'.
  -s             Show information about skipped tests.
  -j <num>       Run <num> jobs in parallel.
  -o <old> <new> Override <old> file path by <new> file path for multiple tests.

<?php
	if ($showHelp) {
		exit(0);
	}
}


// throw unexpected errors/warnings/notices
set_error_handler(function($severity, $message, $file, $line) {
	if (($severity & error_reporting()) === $severity) {
		throw new \ErrorException($message, 0, $severity, $file, $line);
	}
	return FALSE;
});


// Execute tests
try {
	@unlink(__DIR__ . '/coverage.dat'); // @ - file may not exist

	$manager = new TestRunner;
	$manager->parseArguments();
	$res = $manager->run();
	die($res ? 0 : 1);

} catch (Exception $e) {
	echo 'Error: ', $e->getMessage(), "\n";
	die(2);
}
