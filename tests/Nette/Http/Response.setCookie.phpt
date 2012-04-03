<?php

/**
 * Test: Nette\Http\Response::setCookie().
 *
 * @author     David Grudl
 * @package    Nette\Http
 * @subpackage UnitTests
 */

use Nette\Http;



require __DIR__ . '/../bootstrap.php';



$old = headers_list();
$response = new Http\Response;


$response->setCookie('test', 'value', 0);
$headers = array_values(array_diff(headers_list(), $old));
Assert::same( array(
	'Set-Cookie: test=value; path=/; httponly',
), $headers );


$response->setCookie('test', 'newvalue', 0);
$headers = array_values(array_diff(headers_list(), $old));
Assert::same( array(
	/*5.2*'Set-Cookie:',*/
	'Set-Cookie: test=newvalue; path=/; httponly',
), $headers );
