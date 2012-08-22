<?php

/**
 * Test initialization and helpers.
 *
 * @author     David Grudl
 * @package    Nette\Test
 */

require __DIR__ . '/../Test/TestHelpers.php';
require __DIR__ . '/../Test/Assert.php';
require __DIR__ . '/../../Nette/loader.php';


// configure environment
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', TRUE);
ini_set('html_errors', FALSE);
ini_set('log_errors', FALSE);


// create temporary directory
define('TEMP_DIR', __DIR__ . '/../tmp/' . getmypid());
@mkdir(TEMP_DIR, 0777, TRUE);


// catch unexpected errors/warnings/notices
set_error_handler(function($severity, $message, $file, $line) {
	if (($severity & error_reporting()) === $severity) {
		$e = new ErrorException($message, 0, $severity, $file, $line);
		echo "Error: $message in $file:$line\nStack trace:\n" . $e->getTraceAsString();
		exit(TestCase::CODE_ERROR);
	}
	return FALSE;
});


$_SERVER = array_intersect_key($_SERVER, array_flip(array('PHP_SELF', 'SCRIPT_NAME', 'SERVER_ADDR', 'SERVER_SOFTWARE', 'HTTP_HOST', 'DOCUMENT_ROOT', 'OS')));
$_SERVER['REQUEST_TIME'] = 1234567890;
$_ENV = $_GET = $_POST = array();

if (PHP_SAPI !== 'cli') {
	header('Content-Type: text/plain; charset=utf-8');
}


if (extension_loaded('xdebug')) {
	xdebug_disable();
	TestHelpers::startCodeCoverage(__DIR__ . '/coverage.dat');
}


function id($val) {
	return $val;
}
