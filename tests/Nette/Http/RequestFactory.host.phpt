<?php

/**
 * Test: Nette\Http\RequestFactory and host.
 *
 * @author     David Grudl
 * @package    Nette\Http
 */

use Nette\Http\RequestFactory;


require __DIR__ . '/../bootstrap.php';


$_SERVER = array(
	'HTTP_HOST' => 'localhost',
);
$factory = new RequestFactory;
Assert::same( 'http://localhost/', (string) $factory->createHttpRequest()->getUrl() );


$_SERVER = array(
	'HTTP_HOST' => 'www-x.nette.org',
);
$factory = new RequestFactory;
Assert::same( 'http://www-x.nette.org/', (string) $factory->createHttpRequest()->getUrl() );


$_SERVER = array(
	'HTTP_HOST' => '192.168.0.1:8080',
);
$factory = new RequestFactory;
Assert::same( 'http://192.168.0.1:8080/', (string) $factory->createHttpRequest()->getUrl() );


$_SERVER = array(
	'HTTP_HOST' => '[::1aF]:8080',
);
$factory = new RequestFactory;
Assert::same( 'http://[::1af]:8080/', (string) $factory->createHttpRequest()->getUrl() );


$_SERVER = array(
	'HTTP_HOST' => "a.cz\n",
);
$factory = new RequestFactory;
Assert::same( 'http:///', (string) $factory->createHttpRequest()->getUrl() );


$_SERVER = array(
	'HTTP_HOST' => 'AB',
);
$factory = new RequestFactory;
Assert::same( 'http://ab/', (string) $factory->createHttpRequest()->getUrl() );
