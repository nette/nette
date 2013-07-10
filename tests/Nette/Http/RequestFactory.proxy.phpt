<?php

/**
 * Test: Nette\Http\RequestFactory and proxy.
 *
 * @author     David Grudl
 * @package    Nette\Http
 */

use Nette\Http\RequestFactory;


require __DIR__ . '/../bootstrap.php';


test(function() {
	$_SERVER = array(
		'REMOTE_ADDR' => '127.0.0.3',
		'REMOTE_HOST' => 'localhost',
		'HTTP_X_FORWARDED_FOR' => '23.75.345.200, 10.0.0.1',
		'HTTP_X_FORWARDED_HOST' => 'otherhost, anotherhost',
	);

	$factory = new RequestFactory;
	$factory->setProxy('127.0.0.1');
	Assert::same( '127.0.0.3', $factory->createHttpRequest()->getRemoteAddress() );
	Assert::same( 'localhost', $factory->createHttpRequest()->getRemoteHost() );

	$factory->setProxy('127.0.0.1/8');
	Assert::same( '23.75.345.200', $factory->createHttpRequest()->getRemoteAddress() );
	Assert::same( 'otherhost', $factory->createHttpRequest()->getRemoteHost() );
});
