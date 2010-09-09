<?php

/**
 * Test: Nette\Environment variables.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Environment;



require __DIR__ . '/../initialize.php';



Assert::null( Environment::getVariable('foo'), "Getting variable 'foo':" );


try {
	Environment::getVariable('tempDir');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidStateException', "Unknown environment variable 'appDir'.", $e );
}


// Defining constant 'APP_DIR':
define('APP_DIR', '/myApp');

Assert::same( '/myApp', Environment::getVariable('appDir') );


Assert::same( '/myApp/temp', Environment::getVariable('tempDir') );



// Setting variable 'test'...
Environment::setVariable('test', '%appDir%/test');

Assert::same( '/myApp/test', Environment::getVariable('test') );


Assert::same( array(
	'encoding' => 'UTF-8',
	'lang' => 'en',
	'cacheBase' => '/myApp/temp',
	'tempDir' => '/myApp/temp',
	'logDir' => '/myApp/log',
	'appDir' => '/myApp',
	'test' => '/myApp/test',
), Environment::getVariables());



try {
	// Setting circular variables...
	Environment::setVariable('bar', '%foo%');
	Environment::setVariable('foo', '%foobar%');
	Environment::setVariable('foobar', '%bar%');
	Environment::getVariable('bar');

	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidStateException', 'Circular reference detected for variables: foo, foobar, bar.', $e );
}
