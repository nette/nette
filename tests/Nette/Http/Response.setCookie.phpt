<?php

/**
 * Test: Nette\Http\Response::setCookie().
 *
 * @author     David Grudl
 * @package    Nette\Http
 */

use Nette\Http;


require __DIR__ . '/../bootstrap.php';

if (PHP_SAPI === 'cli') {
	Tester\Environment::skip('Cookies are not available in CLI');
}


$old = headers_list();
$response = new Http\Response;


$response->setCookie('test', 'value', 0);
$headers = array_values(array_diff(headers_list(), $old, array('Set-Cookie:')));
Assert::same( array(
	'Set-Cookie: test=value; path=/; httponly',
), $headers );


$response->setCookie('test', 'newvalue', 0);
$headers = array_values(array_diff(headers_list(), $old, array('Set-Cookie:')));
Assert::same( array(
	'Set-Cookie: test=newvalue; path=/; httponly',
), $headers );
