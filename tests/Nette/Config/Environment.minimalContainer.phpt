<?php

/**
 * Test: Nette\Environment minimal usage.
 *
 * @author     David Grudl
 * @package    Nette
 */

use Nette\Environment;



require __DIR__ . '/../bootstrap.php';



Assert::null( Environment::getVariable('foo', NULL), "Getting variable 'foo':" );


Assert::exception(function() {
	Environment::getVariable('foo');
}, 'Nette\InvalidStateException', "Unknown environment variable 'foo'.");


// Defining constant 'APP_DIR':
Environment::setVariable('appDir', '/myApp');
Assert::same( '/myApp', Environment::getVariable('appDir') );


// Setting variable 'test'...
Environment::setVariable('test', '%appDir%/test');
Assert::same( '/myApp/test', Environment::getVariable('test') );


// Services
Assert::same( 'Nette\Http\Response', get_class(Environment::getHttpResponse()) );
Assert::same( 'Nette\Application\Application', get_class(Environment::getApplication()) );
Assert::same( 'Nette\Caching\Cache', get_class(Environment::getCache('my')) );


// Modes
Assert::false( Environment::isConsole(), 'Is console?' );
Assert::true( Environment::isProduction(), 'Is production mode?' );
