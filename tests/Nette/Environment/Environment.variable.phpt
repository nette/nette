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
define('APP_DIR', '/myApp');

Assert::same( '/myApp', Environment::getVariable('appDir') );


// Setting variable 'test'...
Environment::setVariable('test', '%appDir%/test');

Assert::same( '/myApp/test', Environment::getVariable('test') );


Assert::same( array(
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
	Assert::exception('Nette\InvalidStateException', 'Circular reference detected for variables: foo, foobar, bar.', $e );
}
