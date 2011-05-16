<?php

/**
 * Test: Nette\Environment variables.
 *
 * @author     David Grudl
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Environment;



require __DIR__ . '/../bootstrap.php';



Assert::null( Environment::getVariable('foo', NULL), "Getting variable 'foo':" );


try {
	Environment::getVariable('foo');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\InvalidStateException', "Unknown environment variable 'foo'.", $e );
}


// Defining constant 'APP_DIR':
Environment::setVariable('appDir', '/myApp');

Assert::same( '/myApp', Environment::getVariable('appDir') );


// Setting variable 'test'...
Environment::setVariable('test', '%appDir%/test');

Assert::same( '/myApp/test', Environment::getVariable('test') );
