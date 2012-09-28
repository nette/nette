<?php

/**
 * Test initialization and helpers.
 *
 * @author     David Grudl
 * @package    Nette\Test
 */


require __DIR__ . '/../../tools/nette/tester/Tester/bootstrap.php';
require __DIR__ . '/../../Nette/loader.php';


// configure environment
date_default_timezone_set('Europe/Prague');


// temporary directory garbage collection
if (mt_rand() / mt_getrandmax() < 0.01) {
	foreach (glob(__DIR__ . '/../tmp/*[0-9]', GLOB_ONLYDIR) as $dir) {
		if (time() - @filemtime($dir) > 300 && @rename($dir, $dir . '-delete')) {
			TestHelpers::purge($dir . '-delete');
			rmdir($dir . '-delete');
		}
	}
}

// create temporary directory
define('TEMP_DIR', __DIR__ . '/../tmp/' . getmypid());
TestHelpers::purge(TEMP_DIR);


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
