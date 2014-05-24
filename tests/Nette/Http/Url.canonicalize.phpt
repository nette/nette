<?php

/**
 * Test: Nette\Http\Url canonicalize.
 */

use Nette\Http\Url,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$url = new Url('http://hostname/path?arg=value&arg2=v%20a%26l%3Du%2Be');
Assert::same( 'arg=value&arg2=v%20a%26l%3Du%2Be',  $url->query );

$url->canonicalize();
Assert::same( 'arg=value&arg2=v a%26l%3Du%2Be',  $url->query );
