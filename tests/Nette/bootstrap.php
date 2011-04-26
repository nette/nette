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

define('TEMP_DIR', __DIR__ . '/../tmp');

// configure environment
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', TRUE);
ini_set('html_errors', FALSE);
ini_set('log_errors', FALSE);

$_SERVER = array_intersect_key($_SERVER, array_flip(array('PHP_SELF', 'SCRIPT_NAME', 'SERVER_ADDR', 'SERVER_SOFTWARE', 'HTTP_HOST', 'DOCUMENT_ROOT', 'OS')));
$_SERVER['REQUEST_TIME'] = 1234567890;
$_ENV = array();

if (PHP_SAPI !== 'cli') {
	header('Content-Type: text/plain; charset=utf-8');
}

if (extension_loaded('xdebug')) {
	xdebug_disable();
	TestHelpers::startCodeCoverage(__DIR__ . '/coverage.dat');
}
