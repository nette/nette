<?php

/**
 * Test: Nette\Http\RequestFactory and method.
 *
 * @author     David Grudl
 * @package    Nette\Http
 */

use Nette\Http\RequestFactory;


require __DIR__ . '/../bootstrap.php';


$_SERVER = array(
	'REQUEST_METHOD' => 'GET',
	'HTTP_X_HTTP_METHOD_OVERRIDE' => 'PATCH',
);
$factory = new RequestFactory;
Assert::same( 'GET', $factory->createHttpRequest()->getMethod() );


$_SERVER = array(
	'REQUEST_METHOD' => 'POST',
	'HTTP_X_HTTP_METHOD_OVERRIDE' => 'PATCH',
);
$factory = new RequestFactory;
Assert::same( 'PATCH', $factory->createHttpRequest()->getMethod() );


$_SERVER = array(
	'REQUEST_METHOD' => 'POST',
	'HTTP_X_HTTP_METHOD_OVERRIDE' => ' *',
);
$factory = new RequestFactory;
Assert::same( 'POST', $factory->createHttpRequest()->getMethod() );
