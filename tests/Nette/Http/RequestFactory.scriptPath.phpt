<?php

/**
 * Test: Nette\Http\RequestFactory scriptPath detection.
 *
 * @author     David Grudl
 * @package    Nette\Http
 */

use Nette\Http\RequestFactory;


require __DIR__ . '/../bootstrap.php';


$factory = new RequestFactory;

test(function() use ($factory) {
	$_SERVER = array(
		'REQUEST_URI' => '/projects/modules-usage/www/',
		'SCRIPT_FILENAME' => 'W:/projects/Modules-Usage/www/index.php',
		'SCRIPT_NAME' => '/projects/modules-usage/www/index.php',
	);

	Assert::same( '/projects/modules-usage/www/', $factory->createHttpRequest()->getUrl()->getScriptPath() );
});


test(function() use ($factory) {
	$_SERVER = array(
		'REQUEST_URI' => '/projects/modules-usage/www/default/add-item',
		'SCRIPT_FILENAME' => 'W:/projects/Modules-Usage/www/index.php',
		'SCRIPT_NAME' => '/projects/Modules-Usage/www/index.php',
	);

	Assert::same( '/projects/modules-usage/www/', $factory->createHttpRequest()->getUrl()->getScriptPath() );
});


test(function() use ($factory) {
	$_SERVER = array(
		'REQUEST_URI' => '/www/index.php',
		'SCRIPT_FILENAME' => 'w:\projects\modules-usage\www\index.php',
		'SCRIPT_NAME' => '/www/index.php',
	);

	Assert::same( '/www/index.php', $factory->createHttpRequest()->getUrl()->getScriptPath() );
});


test(function() use ($factory) {
	$_SERVER = array(
		'REQUEST_URI' => '/www/',
		'SCRIPT_FILENAME' => 'w:\projects\modules-usage\www\index.php',
		'SCRIPT_NAME' => '/www/',
	);

	Assert::same( '/www/', $factory->createHttpRequest()->getUrl()->getScriptPath() );
});
