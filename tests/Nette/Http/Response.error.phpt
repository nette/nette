<?php

/**
 * Test: Nette\Http\Response errors.
 *
 * @author     David Grudl
 * @package    Nette\Http
 */

use Nette\Http,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

if (PHP_SAPI === 'cli') {
	Tester\Environment::skip('Cookies are not available in CLI');
}


$response = new Http\Response;
$response->setHeader('A', 'b');

Assert::error(function() use ($response) {
	ob_start();
	echo ' ';
	$response->setHeader('A', 'b');
}, E_USER_NOTICE, 'Possible problem: you are sending a HTTP header while already having some data in output buffer%a%');


Assert::exception(function() use ($response) {
	ob_flush(); flush();
	$response->setHeader('A', 'b');
}, 'Nette\InvalidStateException', 'Cannot send header after HTTP headers have been sent (output started at ' . __FILE__ . ':' . (__LINE__ - 2) . ').');
